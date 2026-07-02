<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SebpayService
{
    protected ?string $apiKey;
    protected ?string $publicKey;
    protected ?string $secretKey;
    protected bool $sandbox;

    public function __construct()
    {
        $this->apiKey = config('paiement.methodes.sebpay.api_key');
        $this->publicKey = config('paiement.methodes.sebpay.public_key');
        $this->secretKey = config('paiement.methodes.sebpay.secret_key');
        $this->sandbox = config('paiement.methodes.sebpay.sandbox', true);
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Crée une transaction Sebpay et retourne l'URL de paiement.
     */
    public function createTransaction(float $montant, string $ticketId, string $email, string $nom, ?string $telephone = null): array
    {
        $baseUrl = $this->sandbox ? 'https://sandbox.sebpay.com/api/v1' : 'https://api.sebpay.com/v1';

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post($baseUrl . '/transactions', [
            'amount' => (int) $montant,
            'currency' => 'XOF',
            'description' => 'Ticket #' . $ticketId . ' - PaxEvent',
            'callback_url' => route('paiement.sebpay.callback') . '?ticket=' . $ticketId,
            'customer_email' => $email,
            'customer_name' => $nom,
            'customer_phone' => $telephone,
            'external_id' => (string) $ticketId,
        ]);

        $data = $response->json();

        if ($response->failed()) {
            \Illuminate\Support\Facades\Log::error('Sebpay createTransaction failed', [
                'ticket' => $ticketId,
                'response' => $data,
            ]);
            return ['success' => false, 'error' => $data['message'] ?? 'Erreur Sebpay'];
        }

        return [
            'success' => true,
            'transaction' => $data['transaction'] ?? $data,
            'redirect_url' => $data['redirect_url'] ?? $data['payment_url'] ?? $data['url'] ?? null,
            'transaction_id' => $data['transaction_id'] ?? $data['id'] ?? null,
        ];
    }

    /**
     * Vérifie le statut d'une transaction Sebpay.
     */
    public function verifyTransaction(string $transactionId): array
    {
        $baseUrl = $this->sandbox ? 'https://sandbox.sebpay.com/api/v1' : 'https://api.sebpay.com/v1';

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->get($baseUrl . '/transactions/' . $transactionId);

        $data = $response->json();

        if ($response->failed()) {
            \Illuminate\Support\Facades\Log::error('Sebpay verify failed', [
                'transaction_id' => $transactionId,
                'response' => $data,
            ]);
            return ['success' => false, 'status' => 'failed'];
        }

        $transaction = $data['transaction'] ?? $data;
        $status = $transaction['status'] ?? 'unknown';

        return [
            'success' => in_array(strtolower($status), ['success', 'completed', 'approved', 'confirmed', 'paye']),
            'status' => $status,
            'transaction' => $transaction,
            'channel' => $transaction['channel'] ?? $transaction['payment_method'] ?? 'sebpay',
        ];
    }
}
