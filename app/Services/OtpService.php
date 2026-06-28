<?php

namespace App\Services;

use App\Models\EmailVerification;
use App\Mail\OtpEmail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class OtpService
{
    public function generateAndSend(string $email): void
    {
        EmailVerification::where('email', $email)
            ->whereNull('used_at')
            ->where('expires_at', '>', Carbon::now())
            ->update(['used_at' => Carbon::now()]);

        $code = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);

        EmailVerification::create([
            'email' => $email,
            'code' => Hash::make($code),
            'expires_at' => Carbon::now()->addMinutes(10),
        ]);

        Mail::to($email)->send(new OtpEmail($code));
    }

    public function verify(string $email, string $code): string
    {
        $record = EmailVerification::where('email', $email)
            ->whereNull('used_at')
            ->latest()
            ->first();

        if (!$record) {
            return 'invalide';
        }

        if ($record->expires_at->isPast()) {
            return 'expire';
        }

        if (!Hash::check($code, $record->code)) {
            return 'invalide';
        }

        $record->update(['used_at' => Carbon::now()]);

        return 'valide';
    }
}
