<?php

namespace App\Http\Controllers;

use App\Mail\TicketEmail;
use App\Models\Evenement;
use App\Models\Log;
use App\Models\Ticket;
use App\Services\PaiementMapper;
use App\Services\QrCodeService;
use App\Services\TicketPdfService;
use App\Support\PerPage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class TicketController extends Controller
{
    // Liste les tickets de l'organisateur avec filtres et statistiques
    public function index(Request $request)
    {
        $filtres = $this->filtres($request);
        $evenementsIds = $filtres['evenementsIds'];

        $query = Ticket::with('evenement', 'tarif')->whereIn('evenement_id', $evenementsIds);
        $this->appliquerFiltres($query, $filtres);

        $tickets = $query->orderBy('date_achat', 'desc')->paginate(PerPage::resolve());

        // Statistiques par catégorie de ticket (mêmes filtres)
        $statsQuery = Ticket::whereIn('evenement_id', $evenementsIds);
        $this->appliquerFiltres($statsQuery, $filtres);

        $totalTickets = $statsQuery->count();
        $valides = (clone $statsQuery)->where('statut_paiement', 'payé')->where('utilise', false)->count();
        $scannes = (clone $statsQuery)->where('utilise', true)->count();
        $etudiants = (clone $statsQuery)->where('nom_tarif', 'like', '%tudiant%')->count();
        $annules = (clone $statsQuery)->whereIn('statut_paiement', ['annulé', 'remboursé'])->count();

        // Taux de réussite des transactions FedaPay (hors ventes manuelles et tickets gratuits)
        $transactionsReussies = (clone $statsQuery)->where('statut_paiement', 'payé')
            ->where('transaction_id', 'not like', 'MANUEL-%')
            ->where('transaction_id', 'not like', 'GRATUIT-%')
            ->count();
        $transactionsEchouees = (clone $statsQuery)->where('statut_paiement', 'échoué')->count();
        $transactionsTentees = $transactionsReussies + $transactionsEchouees;
        $tauxReussite = $transactionsTentees > 0 ? round($transactionsReussies / $transactionsTentees * 100, 1) : 0;

        // Alertes : transactions bloquées ou anormales
        $alertes = $this->detecterAlertes($evenementsIds);

        return view('tickets.index', compact(
            'tickets', 'totalTickets', 'valides', 'scannes', 'etudiants', 'annules',
            'transactionsReussies', 'transactionsEchouees', 'tauxReussite', 'alertes', 'filtres'
        ));
    }

    // Exporte les tickets filtrés en CSV
    public function exportCsv(Request $request)
    {
        $filtres = $this->filtres($request);
        $evenementsIds = $filtres['evenementsIds'];

        $query = Ticket::with('evenement')->whereIn('evenement_id', $evenementsIds);
        $this->appliquerFiltres($query, $filtres);

        $tickets = $query->orderBy('date_achat', 'desc')->get();

        $filename = 'tickets-'.date('Y-m-d-Hi').'.csv';

        return response()->streamDownload(function () use ($tickets) {
            $out = fopen('php://output', 'w');

            // BOM UTF-8 pour Excel
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, [
                'Reference', 'Participant', 'Telephone', 'Email', 'Evenement',
                'Tarif', 'Montant (FCFA)', 'Statut', 'Moyen de paiement',
                'Operateur', 'Transaction ID', 'Date achat',
            ], ';');

            foreach ($tickets as $ticket) {
                fputcsv($out, [
                    $ticket->code_unique,
                    $ticket->nom_acheteur,
                    $ticket->telephone_acheteur,
                    $ticket->email_acheteur,
                    $ticket->evenement?->titre ?? '-',
                    $ticket->nom_tarif ?? '-',
                    number_format((float) $ticket->montant, 0, ',', ' '),
                    $ticket->statut_paiement,
                    PaiementMapper::moyenLabel(PaiementMapper::moyenPaiement($ticket->methode_paiement)),
                    PaiementMapper::operateurLabel(PaiementMapper::operateur($ticket->methode_paiement)),
                    $ticket->transaction_id,
                    $ticket->date_achat?->format('d/m/Y H:i'),
                ], ';');
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    // Extrait les paramètres de filtre communs (recherche, période, opérateur, statut)
    private function filtres(Request $request): array
    {
        $user = auth()->user();
        $evenementsIds = Evenement::where('user_id', $user->id)->pluck('id');

        $search = trim((string) $request->input('q'));
        $periode = in_array($request->input('periode'), ['7', '30', '90', 'annee', 'tout'], true)
            ? $request->input('periode')
            : 'tout';
        $operateur = $request->input('operateur');
        $statut = $request->input('statut');

        return compact('evenementsIds', 'search', 'periode', 'operateur', 'statut');
    }

    // Applique les filtres sur une requête (liste ou stats)
    private function appliquerFiltres(Builder $query, array $filtres): void
    {
        if ($filtres['search'] !== '') {
            $s = $filtres['search'];
            $query->where(function (Builder $sub) use ($s) {
                $sub->where('nom_acheteur', 'like', '%'.$s.'%')
                    ->orWhere('telephone_acheteur', 'like', '%'.$s.'%')
                    ->orWhere('email_acheteur', 'like', '%'.$s.'%')
                    ->orWhere('code_unique', 'like', '%'.$s.'%');
            });
        }

        if ($filtres['periode'] !== 'tout') {
            $query->where('date_achat', '>=', match ($filtres['periode']) {
                '7' => now()->subDays(7),
                '30' => now()->subDays(30),
                '90' => now()->subDays(90),
                'annee' => now()->subYear(),
            });
        }

        if ($filtres['operateur']) {
            $query->where(function (Builder $sub) use ($filtres) {
                // Groupe générique mobile money (tout sauf espèces/carte)
                if ($filtres['operateur'] === 'mobile_money') {
                    $sub->where('type_paiement', 'mobile_money');
                } elseif ($filtres['operateur'] === 'especes') {
                    $sub->whereIn('methode_paiement', ['cash', 'especes']);
                } elseif ($filtres['operateur'] === 'bancaire') {
                    $sub->where('type_paiement', 'bancaire');
                } else {
                    $sub->where('methode_paiement', $filtres['operateur'])
                        ->orWhere('methode_paiement', 'like', '%'.$filtres['operateur'].'%');
                }
            });
        }

        if ($filtres['statut']) {
            $query->where('statut_paiement', $filtres['statut']);
        }
    }

    // Détecte les transactions bloquées ou anormales pour l'organisateur
    private function detecterAlertes(iterable $evenementsIds): array
    {
        $alertes = [];

        // 1. Transactions en attente depuis trop longtemps (bloquées)
        $enAttente = Ticket::whereIn('evenement_id', $evenementsIds)
            ->where('statut_paiement', 'en_attente')
            ->where('date_achat', '<', now()->subMinutes(30))
            ->count();
        if ($enAttente > 0) {
            $alertes[] = [
                'type' => 'warning',
                'titre' => $enAttente.' transaction(s) en attente depuis plus de 30 min',
                'message' => 'Certains paiements FedaPay semblent bloqués. Vérifiez qu\'ils ont bien été confirmés, ou réessayez avec le client.',
            ];
        }

        // 2. Tickets payés sans référence de transaction FedaPay (ventes manuelles exceptées)
        $sansTransaction = Ticket::whereIn('evenement_id', $evenementsIds)
            ->where('statut_paiement', 'payé')
            ->whereNull('transaction_id')
            ->count();
        if ($sansTransaction > 0) {
            $alertes[] = [
                'type' => 'danger',
                'titre' => $sansTransaction.' ticket(s) payé(s) sans référence de transaction',
                'message' => 'Ces tickets sont payés mais ne sont rattachés à aucune transaction FedaPay. Ils pourraient résulter d\'une vente manuelle non tracée.',
            ];
        }

        // 3. Forte proportion d'échecs récents (7 derniers jours)
        $reussies = Ticket::whereIn('evenement_id', $evenementsIds)
            ->where('statut_paiement', 'payé')
            ->where('date_achat', '>=', now()->subDays(7))
            ->count();
        $echouees = Ticket::whereIn('evenement_id', $evenementsIds)
            ->where('statut_paiement', 'échoué')
            ->where('date_achat', '>=', now()->subDays(7))
            ->count();
        $total = $reussies + $echouees;
        if ($total >= 5 && $echouees / $total > 0.4) {
            $alertes[] = [
                'type' => 'warning',
                'titre' => 'Taux d\'échec élevé sur les 7 derniers jours ('.round($echouees / $total * 100).' %)',
                'message' => 'Plus de 40 % des paiements récents ont échoué. Un opérateur est peut-être en panne : contactez le support ou vérifiez FedaPay.',
            ];
        }

        return $alertes;
    }

    // Détails d'un ticket avec historique de logs
    public function show(int $id)
    {
        $ticket = Ticket::with('evenement', 'tarif', 'notifications')->findOrFail($id);
        $this->authoriserOrganisateur($ticket); // Contrôle de propriété
        // use two-argument where to avoid argument mismatch
        $logs = Log::where('ticket_id', $id)->orderBy('created_at', 'desc')->get(); // Historique complet

        return view('tickets.show', compact('ticket', 'logs'));
    }

    // Télécharge le PDF du ticket avec QR code
    public function downloadPdf(int $id)
    {
        $ticket = Ticket::with('evenement', 'tarif')->findOrFail($id);
        $this->authoriserOrganisateur($ticket); // Contrôle de propriété

        if ($ticket->download_count >= 3) {
            return back()->with('error', 'Limite de téléchargements atteinte (3 maximum).'); // Anti-abus
        }

        $ticket->increment('download_count', 1, []); // Incrémente le compteur

        $reste = 3 - $ticket->download_count;
        if ($reste === 1) {
            session()->flash('warning', "Attention : il ne vous reste plus qu'1 téléchargement sur les 3 autorisés.");
        }

        $qrCodeDataUri = QrCodeService::generateDataUri($ticket->code_unique, 170);
        $logoDataUri = Ticket::logoVioletDataUri();

        $pdf = TicketPdfService::generer($ticket, $qrCodeDataUri, $logoDataUri);

        $filename = 'PaxEvent-'.$ticket->code_unique.'.pdf';

        return $pdf->download($filename);
    }

    // Télécharge le ticket (avec vérification de paiement)
    public function downloadTicket(int $id)
    {
        $ticket = Ticket::with('evenement', 'tarif')->findOrFail($id);

        if ($ticket->statut_paiement !== 'payé') {
            return back()->with('error', 'Le ticket n\'est pas disponible tant que le paiement n\'est pas confirmé.'); // Paiement requis
        }

        if ($ticket->download_count >= 3) {
            return back()->with('error', 'Limite de téléchargements atteinte (3 maximum).');
        }

        $ticket->increment('download_count', 1, []);

        $reste = 3 - $ticket->download_count;
        if ($reste === 1) {
            session()->flash('warning', "Attention : il ne vous reste plus qu'1 téléchargement sur les 3 autorisés.");
        }

        $qrCodeDataUri = QrCodeService::generateDataUri($ticket->code_unique, 170);
        $logoDataUri = Ticket::logoVioletDataUri();

        $pdf = TicketPdfService::generer($ticket, $qrCodeDataUri, $logoDataUri);

        $filename = 'PaxEvent-'.$ticket->code_unique.'.pdf';

        return $pdf->download($filename);
    }

    // Page de récupération de ticket par le public
    public function recuperer()
    {
        return view('site.recuperer');
    }

    // Recherche de tickets par ID de transaction et email
    public function rechercher(Request $request)
    {
        $request->validate([
            'transaction_id' => 'required|string|max:255',
            'email' => 'required|email',
        ], [
            'transaction_id.required' => 'L\'ID de transaction est requis.',
            'transaction_id.string' => 'L\'ID de transaction doit être une chaîne de caractères.',
            'transaction_id.max' => 'L\'ID de transaction ne peut pas dépasser 255 caractères.',
            'email.required' => 'L\'email est requis.',
            'email.email' => 'L\'email doit être une adresse email valide.',
        ]);

        $tickets = Ticket::with('evenement', 'tarif')
            ->where('transaction_id', $request->transaction_id)
            ->where('email_acheteur', strtolower($request->email))
            ->orderBy('date_achat', 'desc')
            ->get();

        if ($tickets->isEmpty()) {
            return back()->with('error', 'Aucun billet trouvé avec ces informations. Vérifiez l\'ID de transaction et l\'email saisis lors de l\'achat.');
        }

        return view('site.resultats', compact('tickets'));
    }

    // Renvoie le ticket par email depuis l'interface admin
    public function renvoyer(int $id)
    {
        $ticket = Ticket::with('evenement')->findOrFail($id);
        $this->authoriserOrganisateur($ticket); // Contrôle de propriété

        try {
            Mail::to($ticket->email_acheteur)->send(new TicketEmail($ticket));
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l\'envoi du ticket: '.$e->getMessage());
        }

        Log::create([
            'ticket_id' => $ticket->id,
            'type_operation' => 'envoi',
            'details' => json_encode(['methode' => 'renvoi_admin', 'email' => $ticket->email_acheteur]), // Contexte : renvoi admin
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return back()->with('success', 'Ticket renvoyé a '.$ticket->email_acheteur);
    }

    // Annule un ticket et restaure les quotas
    public function annuler(int $id)
    {
        $ticket = Ticket::with('evenement', 'tarif')->findOrFail($id);
        $this->authoriserOrganisateur($ticket); // Contrôle de propriété

        if ($ticket->statut_paiement !== 'payé' && $ticket->statut_paiement !== 'en_attente') {
            return back()->with('error', 'Ce ticket ne peut pas etre annulé.'); // Statut incompatible
        }

        $ancienStatut = $ticket->statut_paiement;

        $ticket->update([
            'statut_paiement' => 'rembourse', // Marque comme remboursé/annulé
        ]);

        if ($ticket->evenement) {
            $ticket->evenement->decrement('quota_vendu', 1, []); // Restaure le quota
        }
        if ($ticket->tarif) {
            $ticket->tarif->decrement('quantite_vendue', 1, []); // Restaure la quantité vendue
        }

        Log::create([
            'ticket_id' => $ticket->id,
            'type_operation' => 'remboursement',
            'details' => json_encode(['ancien_statut' => $ancienStatut, 'motif' => 'annulation_admin']),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return back()->with('success', 'Ticket annulé et quota restauré.');
    }

    public function create()
    {
        return view('tickets.index');
    }

    public function store(Request $request)
    {
        return back();
    }

    public function edit(int $id)
    {
        return back();
    }

    public function update(Request $request, int $id)
    {
        return back();
    }

    public function destroy(int $id)
    {
        return back();
    }

    // Vérifie que le ticket appartient à un événement de l'organisateur connecté
    private function authoriserOrganisateur(Ticket $ticket): void
    {
        $user = auth()->user();
        if (! $user || $user->role === 'super_admin') {
            return; // Le super admin a accès à tout
        }
        if (! $ticket->evenement || $ticket->evenement->user_id !== $user->id) {
            abort(403);
        }
    }
}
