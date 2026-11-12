<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FedapayService
{
    public function getPublicKey(): ?string
    {
        return config('services.fedapay.public_key');
    }

    public function isSandbox(): bool
    {
        return config('services.fedapay.sandbox', true);
    }

    /**
     * Récupère les opérateurs mobile money réellement utilisés sur le compte FedaPay
     * en interrogeant les transactions récentes.
     * Retourne un tableau associatif opérateur => nombre de transactions.
     * Retourne [] si l'API est injoignable ou si aucune transaction mobile money.
     */
    public function getOperateursUtilises(int $limit = 100): array
    {
        $secretKey = config('services.fedapay.secret_key');

        if (! $secretKey) {
            Log::warning('FedapayService::getOperateursUtilises - Clé secrète manquante');

            return [];
        }

        $baseUrl = $this->isSandbox()
            ? 'https://sandbox-api.fedapay.com'
            : 'https://api.fedapay.com';

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$secretKey,
                'Accept' => 'application/json',
            ])->timeout(8)->get("{$baseUrl}/v1/transactions/search", [
                'page' => 1,
                'per_page' => $limit,
            ]);

            if (! $response->successful()) {
                Log::warning('FedapayService::getOperateursUtilises - Échec HTTP', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return [];
            }

            $data = $response->json();
            $transactions = $data['v1/transactions'] ?? $data['transactions'] ?? $data;

            $operateurs = [];
            foreach ((array) $transactions as $tx) {
                $method = $tx['mode'] ?? $tx['payment_method'] ?? null;
                if (is_array($method)) {
                    $method = $method['provider'] ?? $method['name'] ?? null;
                }
                $operateur = PaiementMapper::operateur($method);
                if ($operateur) {
                    $operateurs[$operateur] = ($operateurs[$operateur] ?? 0) + 1;
                }
            }

            return $operateurs;
        } catch (\Exception $e) {
            Log::error('FedapayService::getOperateursUtilises - Exception', [
                'message' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Récupère les détails d'une transaction FedaPay via l'API.
     * Retourne le tableau des données ou null en cas d'échec.
     */
    public function getTransaction(string $transactionId): ?array
    {
        $secretKey = config('services.fedapay.secret_key');

        if (! $secretKey) {
            Log::warning('FedapayService::getTransaction - Clé secrète manquante');

            return null;
        }

        // Sécurité : valider le format du transaction ID (empêche l'injection dans l'URL)
        if (! preg_match('/^[a-zA-Z0-9_-]+$/', $transactionId)) {
            Log::warning('FedapayService::getTransaction - Transaction ID invalide', [
                'transaction_id' => $transactionId,
            ]);

            return null;
        }

        $baseUrl = $this->isSandbox()
            ? 'https://sandbox-api.fedapay.com'
            : 'https://api.fedapay.com';

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$secretKey,
                'Accept' => 'application/json',
            ])->get("{$baseUrl}/v1/transactions/{$transactionId}");

            if ($response->successful()) {
                $data = $response->json();

                // L'API FedaPay enveloppe la ressource : {"v1/transaction": {...}}
                $transaction = $data['v1/transaction'] ?? $data['transaction'] ?? $data;

                return $transaction;
            }

            Log::warning('FedapayService::getTransaction - Échec HTTP', [
                'transaction_id' => $transactionId,
                'status' => $response->status(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('FedapayService::getTransaction - Exception', [
                'transaction_id' => $transactionId,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function baseUrl(): string
    {
        return $this->isSandbox()
            ? 'https://sandbox-api.fedapay.com'
            : 'https://api.fedapay.com';
    }

    private function secretKey(): ?string
    {
        $secretKey = config('services.fedapay.secret_key');

        return $secretKey ?: null;
    }

    /**
     * Crée une transaction FedaPay puis génère son lien de paiement.
     * Retourne les données de la transaction enrichies de `payment_url` et `token`,
     * ou null en cas d'échec.
     */
    public function createTransaction(array $payload): ?array
    {
        $secretKey = $this->secretKey();

        if (! $secretKey) {
            Log::warning('FedapayService::createTransaction - Clé secrète manquante');

            return null;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$secretKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->timeout(15)->post($this->baseUrl().'/v1/transactions', $payload);

            if (! $response->successful()) {
                Log::warning('FedapayService::createTransaction - Échec HTTP', [
                    'status' => $response->status(),
                    'body' => $response->json(),
                ]);

                return null;
            }

            $data = $response->json();
            $transaction = $data['v1/transaction'] ?? $data['transaction'] ?? $data;

            if (! is_array($transaction) || empty($transaction['id'])) {
                return null;
            }

            $tokenData = $this->generateToken((string) $transaction['id']);

            if ($tokenData !== null) {
                $transaction['token'] = $tokenData['token'] ?? null;
                $transaction['payment_url'] = $tokenData['url']
                    ?? $tokenData['payment_url']
                    ?? null;
            }

            return $transaction;
        } catch (\Throwable $e) {
            Log::error('FedapayService::createTransaction - Exception', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Génère le token/lien de paiement d'une transaction existante.
     */
    public function generateToken(string $transactionId): ?array
    {
        $secretKey = $this->secretKey();

        if (! $secretKey || ! preg_match('/^[a-zA-Z0-9_-]+$/', $transactionId)) {
            return null;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$secretKey,
                'Accept' => 'application/json',
            ])->timeout(15)->post($this->baseUrl()."/v1/transactions/{$transactionId}/token", []);

            if (! $response->successful()) {
                Log::warning('FedapayService::generateToken - Échec HTTP', [
                    'transaction_id' => $transactionId,
                    'status' => $response->status(),
                ]);

                return null;
            }

            $data = $response->json();
            $token = $data['v1/token'] ?? $data['token'] ?? $data;

            return is_array($token) ? $token : null;
        } catch (\Throwable $e) {
            Log::error('FedapayService::generateToken - Exception', [
                'transaction_id' => $transactionId,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Recherche la transaction FedaPay associée à une référence marchande
     * (le plus souvent l'id du billet) pour la resynchronisation.
     *
     * FedaPay expose un endpoint dédié `GET /v1/transactions/merchant/{ref}`.
     * On l'utilise en priorité : le filtre `?merchant_reference=` de
     * `/v1/transactions/search` n'est pas garanti, ce qui laissait croire
     * qu'aucune transaction n'existait et provoquait une erreur de doublon au
     * moment de recréer le paiement.
     */
    public function findTransactionByExternalId(string $externalId): ?array
    {
        $secretKey = $this->secretKey();
        $reference = trim($externalId);

        if (! $secretKey || $reference === '') {
            return null;
        }

        // 1) Endpoint officiel par référence marchande.
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$secretKey,
                'Accept' => 'application/json',
            ])->timeout(15)->get(
                $this->baseUrl().'/v1/transactions/merchant/'.rawurlencode($reference)
            );

            if ($response->successful()) {
                $data = $response->json();
                $transaction = $data['v1/transaction']
                    ?? $data['transaction']
                    ?? $data;

                if (is_array($transaction) && ! empty($transaction['id'])) {
                    return $transaction;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('FedapayService::findTransactionByExternalId - Exception (merchant)', [
                'reference' => $reference,
                'message' => $e->getMessage(),
            ]);
        }

        // 2) Repli sur la recherche pleine liste, avec les deux filtres connus.
        foreach (['merchant_reference', 'external_id'] as $champ) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer '.$secretKey,
                    'Accept' => 'application/json',
                ])->timeout(15)->get($this->baseUrl().'/v1/transactions/search', [
                    $champ => $reference,
                ]);

                if (! $response->successful()) {
                    continue;
                }

                $data = $response->json();

                // L'API peut renvoyer une liste directe ou une enveloppe.
                $transactions = null;

                if (is_array($data) && array_is_list($data)) {
                    $transactions = $data;
                } else {
                    $transactions = $data['v1/transactions']
                        ?? $data['transactions']
                        ?? (isset($data['data']) && is_array($data['data']) ? $data['data'] : []);
                }

                if (! is_array($transactions) || $transactions === []) {
                    continue;
                }

                foreach ($transactions as $transaction) {
                    if (! is_array($transaction) || empty($transaction['id'])) {
                        continue;
                    }

                    // Sans filtre fiable côté API, on vérifie nous-mêmes que la
                    // transaction correspond bien à cette référence : sinon on
                    // risquerait de réutiliser celle d'un autre billet.
                    $ref = trim((string) ($transaction['merchant_reference'] ?? ''));
                    $external = trim((string) ($transaction['external_id'] ?? ''));

                    if ($ref === $reference || $external === $reference) {
                        return $transaction;
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('FedapayService::findTransactionByExternalId - Exception', [
                    'external_id' => $reference,
                    'field' => $champ,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        return null;
    }
}
