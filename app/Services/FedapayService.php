<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FedapayService
{
    public function getPublicKey(): ?string
    {
        $key = config('services.fedapay.public_key');

        if (!$key) {
            $superAdmin = \App\Models\User::where('role', 'super_admin')->first();
            $key = $superAdmin->fedapay_public_key ?? null;
        }

        return $key;
    }

    public function isSandbox(): bool
    {
        return config('services.fedapay.sandbox', true);
    }

    /**
     * Récupère les détails d'une transaction FedaPay via l'API.
     * Retourne le tableau des données ou null en cas d'échec.
     */
    public function getTransaction(string $transactionId): ?array
    {
        $secretKey = config('services.fedapay.secret_key');

        if (!$secretKey) {
            Log::warning('FedapayService::getTransaction - Clé secrète manquante');
            return null;
        }

        $baseUrl = $this->isSandbox()
            ? 'https://sandbox.fedapay.com'
            : 'https://api.fedapay.com';

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $secretKey,
                'Accept' => 'application/json',
            ])->get("{$baseUrl}/v1/transactions/{$transactionId}");

            if ($response->successful()) {
                $data = $response->json();
                Log::info('FedapayService::getTransaction - Payload complet', [
                    'transaction_id' => $transactionId,
                    'payload' => $data,
                ]);
                return $data;
            }

            Log::warning('FedapayService::getTransaction - Échec HTTP', [
                'transaction_id' => $transactionId,
                'status' => $response->status(),
                'body' => $response->body(),
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
}
