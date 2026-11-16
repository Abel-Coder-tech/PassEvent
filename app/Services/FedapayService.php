<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FedapayService
{
    // Causes d'échec de la dernière création de transaction, pour que
    // l'appelant puisse afficher un message adapté à l'utilisateur.
    public const ERREUR_CONFIG = 'config';             // clé secrète absente
    public const ERREUR_RESEAU = 'reseau';             // FedaPay ne répond pas (délai, connexion)
    public const ERREUR_INDISPONIBLE = 'indisponible'; // FedaPay répond par une erreur 5xx
    public const ERREUR_REFUS = 'refus';               // FedaPay refuse la demande (4xx, réponse inexploitable)

    /**
     * Statuts FedaPay considérés comme un paiement réellement encaissé.
     */
    public const STATUTS_APPROUVES = ['approved', 'completed', 'accepted'];

    private ?string $derniereErreur = null;

    // Vrai quand la dernière recherche de transactions n'a pas pu aboutir
    // (clé absente, panne réseau, réponse HTTP en erreur). Les appelants
    // doivent alors s'abstenir de conclure « aucun paiement ».
    private bool $rechercheIndisponible = false;

    public function derniereErreur(): ?string
    {
        return $this->derniereErreur;
    }

    public function rechercheIndisponible(): bool
    {
        return $this->rechercheIndisponible;
    }

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
            ])->connectTimeout(5)->timeout(10)->get("{$baseUrl}/v1/transactions/{$transactionId}");

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
        $this->derniereErreur = null;
        $secretKey = $this->secretKey();

        if (! $secretKey) {
            Log::warning('FedapayService::createTransaction - Clé secrète manquante');
            $this->derniereErreur = self::ERREUR_CONFIG;

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
                $this->derniereErreur = $response->serverError()
                    ? self::ERREUR_INDISPONIBLE
                    : self::ERREUR_REFUS;

                return null;
            }

            $data = $response->json();
            $transaction = $data['v1/transaction'] ?? $data['transaction'] ?? $data;

            if (! is_array($transaction) || empty($transaction['id'])) {
                $this->derniereErreur = self::ERREUR_REFUS;

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
            $this->derniereErreur = self::ERREUR_RESEAU;

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

        // 2) Repli sur la liste des transactions récentes.
        // Les filtres `?merchant_reference=` et `?external_id=` de l'endpoint de
        // recherche ne sont pas fiables : on pagine et on compare nous-mêmes les
        // références (dont les custom_metadata du checkout public, qui n'envoie
        // jamais de merchant_reference).
        return $this->transactionLaPlusProbante(
            $this->correspondantes($this->transactionsRecentes(3), $reference)
        );
    }

    /**
     * Parcourt les transactions récentes via l'endpoint de recherche FedaPay.
     *
     * L'API ne garantit pas de filtre côté serveur : on récupère les pages et on
     * filtre en PHP. Les doublons d'id sont écartés (la liste peut bouger entre
     * deux pages pendant qu'on la parcourt).
     *
     * @return array<int, array<string, mixed>>
     */
    public function transactionsRecentes(int $maxPages = 20, int $perPage = 100): array
    {
        $secretKey = $this->secretKey();
        $this->rechercheIndisponible = false;

        if (! $secretKey) {
            $this->rechercheIndisponible = true;
            Log::warning('FedapayService::transactionsRecentes - Clé secrète manquante');

            return [];
        }

        $transactions = [];
        $maxPages = max(1, $maxPages);
        $perPage = max(1, $perPage);

        for ($page = 1; $page <= $maxPages; $page++) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer '.$secretKey,
                    'Accept' => 'application/json',
                ])->timeout(30)->get($this->baseUrl().'/v1/transactions/search', [
                    'per_page' => $perPage,
                    'page' => $page,
                ]);

                if (! $response->successful()) {
                    $this->rechercheIndisponible = true;
                    Log::warning('FedapayService::transactionsRecentes - Échec HTTP', [
                        'page' => $page,
                        'status' => $response->status(),
                    ]);

                    break;
                }

                $data = $response->json();
                $liste = null;

                // L'API peut renvoyer une liste directe ou une enveloppe.
                if (is_array($data) && array_is_list($data)) {
                    $liste = $data;
                } elseif (is_array($data)) {
                    $liste = $data['v1/transactions']
                        ?? $data['transactions']
                        ?? (isset($data['data']) && is_array($data['data']) ? $data['data'] : []);
                }

                if (! is_array($liste) || $liste === []) {
                    break;
                }

                foreach ($liste as $transaction) {
                    if (! is_array($transaction) || empty($transaction['id'])) {
                        continue;
                    }

                    $transactions[(string) $transaction['id']] = $transaction;
                }

                if (count($liste) < $perPage) {
                    break;
                }
            } catch (\Throwable $e) {
                $this->rechercheIndisponible = true;
                Log::error('FedapayService::transactionsRecentes - Exception', [
                    'page' => $page,
                    'message' => $e->getMessage(),
                ]);

                break;
            }
        }

        return array_values($transactions);
    }

    /**
     * Transactions FedaPay approuvées depuis une date donnée.
     *
     * @return array<int, array<string, mixed>>
     */
    public function transactionsApprouvees(\DateTimeInterface $depuis, int $maxPages = 5): array
    {
        $approuvees = [];

        foreach ($this->transactionsRecentes($maxPages) as $transaction) {
            $statut = strtolower((string) ($transaction['status'] ?? ''));

            if (! in_array($statut, self::STATUTS_APPROUVES, true)) {
                continue;
            }

            $date = $transaction['created_at'] ?? null;

            if ($date !== null) {
                try {
                    if (\Illuminate\Support\Carbon::parse($date)->lt($depuis)) {
                        continue;
                    }
                } catch (\Throwable) {
                    // Date illisible : on conserve la transaction plutôt que de l'ignorer.
                }
            }

            $approuvees[] = $transaction;
        }

        return $approuvees;
    }

    /**
     * Références exploitables d'une transaction, quels que soient les champs
     * remplis par FedaPay (checkout public, agent, lien de paiement…).
     *
     * @param  array<string, mixed>  $transaction
     * @return array{id: string, external_id: string, ticket_id: string, groupe: string, reference_marchande: string}
     */
    public function referencesTransaction(array $transaction): array
    {
        $metadata = $transaction['custom_metadata'] ?? [];

        if (! is_array($metadata)) {
            $metadata = [];
        }

        return [
            'id' => trim((string) ($transaction['id'] ?? '')),
            'external_id' => trim((string) ($transaction['external_id'] ?? '')),
            'ticket_id' => trim((string) ($metadata['ticket_id'] ?? '')),
            'groupe' => trim((string) ($metadata['group_transaction_id'] ?? '')),
            'reference_marchande' => trim((string) ($transaction['merchant_reference'] ?? '')),
        ];
    }

    /**
     * Retrouve une transaction approuvée à partir de plusieurs références
     * (id de billet, groupe GRP-…, external_id, référence marchande).
     *
     * Utilisé par la purge : avant de libérer une place dont la transaction
     * n'a jamais été enregistrée, on vérifie qu'un paiement approuvé n'existe
     * pas déjà chez FedaPay.
     *
     * @param  array<int, string|null>  $references
     * @param  array<int, array<string, mixed>>  $transactions
     * @return array<string, mixed>|null
     */
    public function transactionPourReferences(array $references, array $transactions): ?array
    {
        $references = array_values(array_unique(array_filter(
            array_map(fn ($reference) => trim((string) $reference), $references),
            fn (string $reference) => $reference !== ''
        )));

        if ($references === []) {
            return null;
        }

        $trouvees = [];

        foreach ($transactions as $transaction) {
            $refs = $this->referencesTransaction($transaction);
            $connues = [
                $refs['external_id'],
                $refs['ticket_id'],
                $refs['groupe'],
                $refs['reference_marchande'],
            ];

            if (array_intersect($references, array_filter($connues, fn (string $v) => $v !== '')) !== []) {
                $trouvees[] = $transaction;
            }
        }

        return $this->transactionLaPlusProbante($trouvees);
    }

    /**
     * Filtre les transactions qui référencent la valeur demandée.
     *
     * @param  array<int, array<string, mixed>>  $transactions
     * @return array<int, array<string, mixed>>
     */
    protected function correspondantes(array $transactions, string $reference): array
    {
        $reference = trim($reference);

        if ($reference === '') {
            return [];
        }

        $trouvees = [];

        foreach ($transactions as $transaction) {
            $refs = $this->referencesTransaction($transaction);

            if (in_array($reference, [
                $refs['external_id'],
                $refs['ticket_id'],
                $refs['groupe'],
                $refs['reference_marchande'],
            ], true)) {
                $trouvees[] = $transaction;
            }
        }

        return $trouvees;
    }

    /**
     * Choisit la transaction la plus probable : un paiement approuvé prime sur
     * une tentative échouée, puis la plus récente.
     *
     * @param  array<int, array<string, mixed>>  $transactions
     * @return array<string, mixed>|null
     */
    protected function transactionLaPlusProbante(array $transactions): ?array
    {
        if ($transactions === []) {
            return null;
        }

        usort($transactions, function (array $a, array $b) {
            $approuveA = in_array(strtolower((string) ($a['status'] ?? '')), self::STATUTS_APPROUVES, true);
            $approuveB = in_array(strtolower((string) ($b['status'] ?? '')), self::STATUTS_APPROUVES, true);

            if ($approuveA !== $approuveB) {
                return $approuveA ? -1 : 1;
            }

            return strcmp((string) ($b['created_at'] ?? ''), (string) ($a['created_at'] ?? ''));
        });

        return $transactions[0];
    }
}
