<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class KkiaPayService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $signature;
    protected string $appId;

    public function __construct()
    {
        $isSandbox = config('services.kkiapay.sandbox', true);
        $this->baseUrl = $isSandbox ? 'https://api.sandbox.kkiapay.me' : 'https://api.kkiapay.me';
        $this->apiKey = config('services.kkiapay.api_key');
        $this->signature = config('services.kkiapay.signature');
        $this->appId = config('services.kkiapay.app_id');
    }

    public function isSandbox(): bool
    {
        return config('services.kkiapay.sandbox', true);
    }

    public function getSdkConfig(): array
    {
        return [
            'api_key' => $this->apiKey,
            'signature' => $this->signature,
            'app_id' => $this->appId,
            'sandbox' => $this->isSandbox(),
        ];
    }

    public function initiatePayment(float $montant, string $phone, string $operator, string $ticketId, string $email): array
    {
        $response = Http::withHeaders([
            'X-API-KEY' => $this->apiKey,
            'X-SIGNATURE' => $this->signature,
        ])->post($this->baseUrl . '/api/v1/m/mobilemoney', [
            'amount' => (string) $montant,
            'phone' => $phone,
            'operator' => $operator,
            'external_id' => $ticketId,
            'email' => $email,
        ]);

        return $response->json();
    }

    public function verifyTransaction(string $transactionId): array
    {
        $response = Http::withHeaders([
            'X-API-KEY' => $this->apiKey,
            'X-SIGNATURE' => $this->signature,
        ])->get($this->baseUrl . '/api/v1/m/mobilemoney/verify/' . $transactionId);

        return $response->json();
    }
}
