<?php
$file = '/home/abel/Projects/PassEvent/app/Http/Controllers/Api/AgentDashboardController.php';
$content = file_get_contents($file);
$old = "        return response()->json([
            'event' => [
                'titre' => \$evenement?->titre,
                'date_event' => \$evenement && \$evenement->date_event ? \$evenement->date_event->toDateString() : null,
                'lieu' => \$evenement?->lieu,
            ],
            'organisateur' => \$organisateur ? [
                'nom' => trim((\$organisateur->prenom ?? '').' '.\$organisateur->nom),
                'email' => \$organisateur->email,
            ] : null,
            'stats' => [
                'total' => \$total,
                'valides' => \$valides,
                'invalides' => \$invalides,
                'deja_utilises' => \$stats['deja_utilises'],
            ],
            'scans' => \$scans->map(function (\$log) {
                return [
                    'date' => \$log->created_at->toIso8601String(),
                    'code' => \$log->details['code'] ?? '',
                    'resultat' => \$log->details['resultat'] ?? 'invalide',
                    'raison' => \$log->details['raison'] ?? null,
                ];
            })->values(),
            'scan_disponible' => (bool) (\$agent->actif && \$evenement && \$evenement->date_event >= now()),
            'notifications' => \$notifications,
        ], 200);";
$new = "        \$offlineLease = \App\Models\OfflineLease::where('evenement_id', \$agent->evenement_id)->whereIn('statut', ['autorise', 'arme', 'activation_en_attente', 'actif', 'revocation_en_attente'])->latest()->first();
        return response()->json([
            'event' => [
                'titre' => \$evenement?->titre,
                'date_event' => \$evenement && \$evenement->date_event ? \$evenement->date_event->toDateString() : null,
                'lieu' => \$evenement?->lieu,
            ],
            'organisateur' => \$organisateur ? [
                'nom' => trim((\$organisateur->prenom ?? '').' '.\$organisateur->nom),
                'email' => \$organisateur->email,
            ] : null,
            'stats' => [
                'total' => \$total,
                'valides' => \$valides,
                'invalides' => \$invalides,
                'deja_utilises' => \$stats['deja_utilises'],
            ],
            'scans' => \$scans->map(function (\$log) {
                return [
                    'date' => \$log->created_at->toIso8601String(),
                    'code' => \$log->details['code'] ?? '',
                    'resultat' => \$log->details['resultat'] ?? 'invalide',
                    'raison' => \$log->details['raison'] ?? null,
                ];
            })->values(),
            'scan_disponible' => (bool) (\$agent->actif && \$evenement && \$evenement->date_event >= now()),
            'notifications' => \$notifications,
            'offline' => [
                'enabled' => (bool) (\$evenement?->offline_mode_enabled),
                'lease_state' => \$offlineLease?->statut,
                'is_holder' => \$offlineLease && (int) \$offlineLease->agent_id === (int) \$agent->id,
                'expires_at' => \$offlineLease?->expires_at?->toIso8601String(),
                'pending_scans' => 0,
                'last_sync_at' => \$offlineLease?->last_sync_at?->toIso8601String(),
            ],
        ], 200);";
if (strpos($content,$old)!==false){ $content=str_replace($old,$new,$content); file_put_contents($file,$content); echo "ok"; } else { echo "no"; }
