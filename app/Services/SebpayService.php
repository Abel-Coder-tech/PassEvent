<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SebpayService
{
    protected ?string $publicKey;
    protected ?string $secretKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->publicKey = config('paiement.methodes.sebpay.public_key');
        $this->secretKey = config('paiement.methodes.sebpay.secret_key');
        $sandbox = config('paiement.methodes.sebpay.sandbox', true);

        $this->baseUrl = $sandbox
            ? config('paiement.methodes.sebpay.sandbox_url', 'https://sandbox-api.sebpay.bj/api/v1')
            : config('paiement.methodes.sebpay.api_url', 'https://newapi.sebpay.bj/api/v1');
    }

    public function isConfigured(): bool
    {
        return !empty($this->publicKey) && !empty($this->secretKey);
    }

    /**
     * Initie une collecte Sebpay.
     */
    public function createTransaction(
        float $montant,
        string $externalReference,
        string $phone,
        string $operator,
        string $country = 'BJ',
        ?string $callbackUrl = null,
        ?string $otpCode = null
    ): array {
        $payload = [
            'amount' => (int) round($montant),
            'currency' => 'XOF',
            'phone' => ltrim($phone, '+'),
            'operator' => $operator,
            'country' => strtoupper($country),
            'external_reference' => $externalReference,
        ];

        if ($callbackUrl) {
            $payload['callback_url'] = $callbackUrl;
        }

        if ($otpCode) {
            $payload['otp_code'] = $otpCode;
        }

        $response = Http::withHeaders([
            'X-Public-Key' => $this->publicKey,
            'X-Secret-Key' => $this->secretKey,
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl . '/collections', $payload);

        $body = $response->json();

        if ($response->failed() || !($body['success'] ?? false)) {
            Log::error('Sebpay collection failed', [
                'payload' => $payload,
                'status' => $response->status(),
                'response' => $body,
            ]);
            return [
                'success' => false,
                'error' => $body['message'] ?? $body['error'] ?? 'Erreur Sebpay',
            ];
        }

        $data = $body['data'] ?? $body;

        return [
            'success' => true,
            'transaction_id' => $data['transaction_id'] ?? null,
            'status' => $data['status'] ?? 'pending',
            'provider_link' => $data['provider_link'] ?? null,
            'message' => $body['message'] ?? $data['message'] ?? '',
            'data' => $data,
        ];
    }

    /**
     * Récupère les détails d'une transaction.
     */
    public function getTransaction(string $idOrReference): array
    {
        $response = Http::withHeaders([
            'X-Public-Key' => $this->publicKey,
            'X-Secret-Key' => $this->secretKey,
        ])->get($this->baseUrl . '/collections/' . $idOrReference);

        $body = $response->json();

        if ($response->failed() || !($body['success'] ?? false)) {
            Log::error('Sebpay getTransaction failed', [
                'reference' => $idOrReference,
                'status' => $response->status(),
                'response' => $body,
            ]);
            return [
                'success' => false,
                'status' => 'failed',
                'error' => $body['message'] ?? 'Erreur Sebpay',
            ];
        }

        $data = $body['data'] ?? $body;

        return [
            'success' => $data['status'] === 'approved',
            'status' => $data['status'] ?? 'unknown',
            'transaction_id' => $data['transaction_id'] ?? $idOrReference,
            'external_reference' => $data['external_reference'] ?? null,
            'amount' => $data['amount'] ?? null,
            'currency' => $data['currency'] ?? null,
            'customer_phone' => $data['customer_phone'] ?? $data['phone'] ?? null,
            'data' => $data,
        ];
    }

    /**
     * Vérifie la signature HMAC-SHA256 d'un webhook Sebpay.
     */
    public function verifyWebhookSignature(string $payloadJson, string $signature): bool
    {
        $expected = hash_hmac('sha256', $payloadJson, $this->secretKey);
        return hash_equals($expected, $signature);
    }
}
