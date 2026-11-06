<?php

namespace App\Support;

use App\Models\OrganizerActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Throwable;

class ActivityLogger
{
    public static function success(string $action, ?string $description = null, ?Model $subject = null, array $details = [], ?Request $request = null): void
    {
        self::record($action, 'success', $description, $subject, $details, $request);
    }

    public static function failure(string $action, ?string $description = null, ?Model $subject = null, array $details = [], ?Request $request = null): void
    {
        self::record($action, 'failure', $description, $subject, $details, $request);
    }

    public static function record(string $action, string $status, ?string $description = null, ?Model $subject = null, array $details = [], ?Request $request = null): void
    {
        try {
            $req = $request ?: request();
            OrganizerActivityLog::create([
                'user_id' => auth()->id(),
                'action' => $action,
                'status' => $status,
                'subject_type' => $subject ? $subject::class : null,
                'subject_id' => $subject?->getKey(),
                'description' => $description ? mb_substr($description, 0, 500) : null,
                'details' => $details ?: null,
                'ip' => $req?->ip(),
                'user_agent' => $req?->userAgent() ? mb_substr((string) $req->userAgent(), 0, 500) : null,
                'created_at' => now(),
            ]);
        } catch (Throwable $e) {
            // Le logging ne doit jamais bloquer l'action métier.
        }
    }
}