<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\Ticket;
use App\Mail\TicketEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class LogController extends Controller
{
    // Liste les logs d'activité avec filtres par type, recherche et période
    public function index(Request $request)
    {
        $type = $request->input('type');
        $q = $request->input('q');
        $periode = $request->input('periode', '30'); // Défaut : 30 jours

        $startDate = $this->getStartDate($periode);

        $query = Log::with('ticket.evenement')
            ->where('created_at', '>=', $startDate);

        // Restreint aux événements de l'organisateur sauf pour le super admin
        if (auth()->user()->role !== 'super_admin') {
            $query->whereHas('ticket.evenement', fn($q) => $q->where('user_id', auth()->id()));
        }

        if ($type) {
            $query->where('type_operation', $type);
        }

        if ($q) {
            $query->where(function ($sub) use ($q) {
                $sub->whereHas('ticket', function ($t) use ($q) {
                    $t->where('nom_acheteur', 'like', '%' . $q . '%')
                        ->orWhere('email_acheteur', 'like', '%' . $q . '%')
                        ->orWhere('code_unique', 'like', '%' . $q . '%');
                })
                ->orWhere('ip', 'like', '%' . $q . '%');
            });
        }

        $logs = $query->orderByDesc('created_at')->paginate(20);

        $stats = $this->computeStats($startDate);

        $types = [
            'achat' => ['label' => 'Achat', 'color' => 'var(--violet)'],
            'envoi' => ['label' => 'Envoi ticket', 'color' => 'var(--vert)'],
            'scan' => ['label' => 'Scan', 'color' => 'var(--teal)'],
            'recuperation' => ['label' => 'Récupération', 'color' => 'var(--aubergine)'],
            'echec_paiement' => ['label' => 'Paiement échoué', 'color' => 'var(--danger)'],
            'remboursement' => ['label' => 'Remboursement', 'color' => '#f39c12'],
            'code_promo' => ['label' => 'Code promo', 'color' => 'var(--menthe)'],
        ];

        return view('admin.logs.index', compact('logs', 'stats', 'types', 'type', 'q', 'periode'));
    }

    // Affiche les détails d'un log en JSON
    public function detail($id)
    {
        $log = Log::with(['ticket.evenement', 'ticket.tarif'])->findOrFail($id);

        // Vérification de propriété (sauf super admin)
        if (auth()->user()->role !== 'super_admin') {
            if (!$log->ticket || !$log->ticket->evenement || $log->ticket->evenement->user_id !== auth()->id()) {
                abort(403);
            }
        }

        return response()->json([
            'id' => $log->id,
            'type_operation' => $log->type_operation,
            'created_at' => $log->created_at->format('d/m/Y H:i:s'),
            'ip' => $log->ip,
            'user_agent' => $log->user_agent,
            'details' => $log->details,
            'ticket' => $log->ticket ? [
                'code_unique' => $log->ticket->code_unique,
                'nom_acheteur' => $log->ticket->nom_acheteur,
                'email_acheteur' => $log->ticket->email_acheteur,
                'evenement' => $log->ticket->evenement ? $log->ticket->evenement->titre : null,
            ] : null,
        ]);
    }

    // Récupère et renvoie un ticket par téléphone/email depuis l'interface admin
    public function recuperer(Request $request)
    {
        $validated = $request->validate([
            'telephone' => 'required|string|min:6',
            'email' => 'required|email',
        ]);

        $ticket = Ticket::where('telephone_acheteur', $validated['telephone'])
            ->where('email_acheteur', $validated['email'])
            ->where('statut_paiement', 'payé')
            ->with('evenement')
            ->first();

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun ticket trouvé avec ces informations.',
            ], 404);
        }

        try {
            Mail::to($ticket->email_acheteur)->send(new TicketEmail($ticket));

            Log::create([
                'ticket_id' => $ticket->id,
                'type_operation' => 'envoi',
                'details' => json_encode([
                    'canal' => 'email',
                    'email' => $ticket->email_acheteur,
                    'contexte' => 'recuperation_admin',
                ]),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return response()->json([
                'success' => true,
                'ticket' => [
                    'code' => $ticket->code_unique,
                    'evenement' => $ticket->evenement->titre,
                    'nom' => $ticket->nom_acheteur,
                    'email' => $ticket->email_acheteur,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'envoi du ticket.',
            ], 500);
        }
    }

    // Calcule la date de début selon la période sélectionnée
    private function getStartDate(string $periode): \Carbon\Carbon
    {
        return match ($periode) {
            '7' => now()->copy()->subDays(7),
            '30' => now()->copy()->subDays(30),
            '90' => now()->copy()->subDays(90),
            'annee' => now()->copy()->startOfYear(),
            default => now()->copy()->subYears(10),
        };
    }

    // Calcule les statistiques agrégées des logs pour la période donnée
    private function computeStats(\Carbon\Carbon $startDate): array
    {
        $baseQuery = Log::where('created_at', '>=', $startDate);

        if (auth()->user()->role !== 'super_admin') {
            $baseQuery->whereHas('ticket.evenement', fn($q) => $q->where('user_id', auth()->id()));
        }

        $clone = fn() => clone $baseQuery;
        $total = $clone()->count();
        $achats = $clone()->where('type_operation', 'achat')->count();
        $envois = $clone()->where('type_operation', 'envoi')->count();
        $scans = $clone()->where('type_operation', 'scan')->count();
        $echecs = $clone()->where('type_operation', 'echec_paiement')->count();

        return [
            'total' => $total,
            'achats' => $achats,
            'envois' => $envois,
            'scans' => $scans,
            'echecs' => $echecs,
        ];
    }
}
