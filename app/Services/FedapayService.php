<?php

namespace App\Services;

class FedapayService
{
    public function getPublicKey(): ?string
    {
        $key = config('services.fedapay.public_key');

        if (!$key) {
            $superAdmin = \App\Models\User::where('role', '=', 'super_admin', '=')->first();
            $key = $superAdmin->fedapay_public_key ?? null;
        }

        return $key;
    }

    public function isSandbox(): bool
    {
        return config('services.fedapay.sandbox', true);
    }
}
