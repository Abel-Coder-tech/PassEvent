<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Evenement;
use App\Models\LotPhysique;
use App\Models\Tarif;
use App\Models\Ticket;
use App\Services\TicketStatisticsService;
use App\Services\LotAutoService;
use App\Services\LotPhysiquePdfService;
use App\Services\LotPhysiqueTemplatePdfService;
use App\Support\PerPage;
use App\Support\UploadError;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log as FacadesLog;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LotPhysiqueController extends Controller
{
    // Page « Vente physique » de l'organisateur avec mini-dashboard
    public function index(TicketStatisticsService $ticketStatistics)
    {
        $user = Auth::user();
        $evenementIds = $user->evenements()->pluck('id');

        $lots = LotPhysique::with('evenement', 'tarif')
            ->withCount(['tickets as nb_tickets'])
            ->withCount(['tickets as nb_annules' => fn ($q) => $q->where('annule', true)])
            ->withCount(['tickets as nb_scannes' => fn ($q) => $q->where('utilise', true)])
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(PerPage::resolve());

        $ticketsPhysiques = Ticket::whereIn('evenement_id', $evenementIds)
            ->whereNotNull('lot_physique_id')
            ->where('statut_paiement', 'payé');

        $statsTicketsPhysiques = $ticketStatistics->physiques($ticketsPhysiques);
        $nbTickets = $statsTicketsPhysiques['total'];
        $nbAnnules = $statsTicketsPhysiques['annules'];
        $nbScannes = $statsTicketsPhysiques['scannes'];

        // Statistiques des tickets physiques valides (hors annulés) :
        // valeur, commission attendue (guichet) et commission déjà réglée (QR codes/demandes)
        $physique = $user->statsPhysiques();

        return view('admin.lots-physiques.index', [
            'lots' => $lots,
            'nbTickets' => $nbTickets,
            'nbAnnules' => $nbAnnules,
            'nbScannes' => $nbScannes,
            'recettesPhysiques' => $physique['valeur'],
            'commissionTotale' => $physique['commissionTotale'],
            'evenementsAuto' => $this->evenementsAuto($user),
            'tauxCommission' => LotPhysique::TAUX_AUTO,
            'emailDefaut' => $user->email,
        ]);
    }

    // Événements éligibles à l'auto-génération (à venir, avec au moins un tarif actif)
    private function evenementsAuto($user)
    {
        return $user->evenements()
            ->where(fn ($q) => $q->whereNull('date_event')->orWhere('date_event', '>=', now()))
            ->orderBy('date_event')
            ->with('tarifs')
            ->get()
            ->filter(fn ($e) => $e->tarifs->contains(fn ($t) => $t->statut === 'actif'))
            ->values()
            ->map(function ($evenement) {
                $actifs = $evenement->tarifs->where('statut', 'actif')->values();

                return [
                    'id' => $evenement->id,
                    'titre' => $evenement->titre,
                    'date_event' => optional($evenement->date_event)->format('d/m/Y'),
                    'gratuit' => (bool) $evenement->gratuit,
                    'tarifs' => $actifs->map(fn ($t) => [
                        'id' => $t->id,
                        'nom' => $t->nom,
                        'prix' => (float) $t->prix,
                    ])->values()->all(),
                ];
            });
    }

    // Crée la commande (lots en attente de paiement) puis redirige vers le checkout FedaPay.
    // AUCUN ticket n'est créé ici : ils ne le seront qu'après vérification du paiement.
    public function commander(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'evenement_id' => 'required|integer',
            'quantites' => 'required|array|min:1',
            'quantites.*' => 'integer|min:0|max:2000',
            'email_reception' => 'nullable|email|max:191',
        ], [
            'email_reception.email' => 'L\'email de réception doit être une adresse valide.',
            'quantites.*.max' => 'Maximum 2000 tickets par tarif.',
            'quantites.*.min' => 'Quantité invalide.',
            'quantites.*.required' => 'La quantité est obligatoire.',
            'quantites.required' => 'Veuillez indiquer au moins une quantité.',
            'evenement_id.required' => 'Veuillez sélectionner un événement.',
            'evenement_id.integer' => 'Événement invalide.',

        ]);

        $evenement = Evenement::where('user_id', $user->id)->find($data['evenement_id']);
        if (! $evenement || ($evenement->date_event && $evenement->date_event->isPast())) {
            return back()->with('error', 'Événement introuvable ou déjà passé.');
        }

        $lignes = [];
        foreach ($data['quantites'] as $tarifId => $qte) {
            $qte = (int) $qte;
            if ($qte <= 0) {
                continue;
            }
            $tarif = $evenement->tarifs()->where('statut', 'actif')->where('id', $tarifId)->first();
            if (! $tarif) {
                return back()->with('error', 'Un tarif sélectionné n\'est plus disponible.');
            }
            $lignes[] = ['tarif' => $tarif, 'quantite' => $qte];
        }

        if (empty($lignes)) {
            return back()->with('error', 'Indiquez au moins une quantité.');
        }

        // Purge des commandes abandonnées (> 24 h sans transaction FedaPay)
        LotPhysique::where('statut', 'en_attente_paiement')
            ->whereNull('fedapay_transaction_id')
            ->where('created_at', '<', now()->subDay())
            ->delete();

        $reference = 'LOTAUTO-'.strtoupper(Str::random(10));
        $emailReception = trim($data['email_reception'] ?? '') !== '' ? $data['email_reception'] : $user->email;

        DB::transaction(function () use ($lignes, $evenement, $user, $reference, $emailReception) {
            foreach ($lignes as $ligne) {
                LotPhysique::create([
                    'user_id' => $user->id,
                    'evenement_id' => $evenement->id,
                    'tarif_id' => $ligne['tarif']->id,
                    'commission_pourcentage' => null,
                    'nom' => mb_substr('QR Auto - '.$ligne['tarif']->nom, 0, 100),
                    'quantite' => $ligne['quantite'],
                    'statut' => LotPhysique::STATUT_ATTENTE_PAIEMENT,
                    'auto_genere' => true,
                    'montant_commission' => round((float) $ligne['tarif']->prix * (LotPhysique::TAUX_AUTO / 100) * $ligne['quantite'], 2),
                    'email_reception' => $emailReception,
                    'reference_paiement' => $reference,
                ]);
            }
        });

        $total = round(collect($lignes)->sum(fn ($l) => (float) $l['tarif']->prix * (LotPhysique::TAUX_AUTO / 100) * $l['quantite']), 2);

        // Événement gratuit ou tarifs à 0 F : rien à payer, génération immédiate
        if ($total <= 0) {
            $lots = LotPhysique::where('reference_paiement', $reference)->get();
            LotAutoService::confirmerLots($lots, 'GRATUIT-'.$reference);
            LotAutoService::notifierPaiementAccepte($lots);

            return redirect()->route('admin.lots-physiques.index')
                ->with('qr_succes', LotAutoService::donneesResultat($reference));
        }

        return redirect()->route('admin.lots-physiques.checkout', $reference);
    }

    // Checkout FedaPay de la commande (récap + bouton payer)
    // Commun aux deux parcours : commande « Générer mes QR codes » et demande au super admin.
    public function checkout(string $reference)
    {
        $lots = LotPhysique::with('tarif')
            ->where('reference_paiement', $reference)
            ->where('user_id', Auth::id())
            ->get();

        if ($lots->isEmpty()) {
            return redirect()->route('admin.lots-physiques.index')
                ->with('error', 'Commande introuvable.');
        }

        $premier = $lots->first();

        if ($premier->statut !== LotPhysique::STATUT_ATTENTE_PAIEMENT) {
            // Demande déjà payée : le super admin prend en charge la génération
            if ($premier->estUneDemande()) {
                return redirect()->route('admin.lots-physiques.index')
                    ->with('success', 'Cette demande est déjà payée. Le super admin génère vos QR codes et vous les transmettra.');
            }

            return redirect()->route('admin.lots-physiques.index')
                ->with('success', 'Cette commande a déjà été payée. Vos planches sont disponibles ci-dessous.');
        }

        $total = round((float) $lots->sum('montant_commission'), 2);
        $publicKey = app(\App\Services\FedapayService::class)->getPublicKey();
        $sandbox = app(\App\Services\FedapayService::class)->isSandbox();
        $estDemande = $premier->estUneDemande();

        return view('admin.lots-physiques.paiement', compact('lots', 'total', 'reference', 'publicKey', 'sandbox', 'estDemande'));
    }

    // Télécharge la planche de QR codes (3 téléchargements max, lot transmis requis)
    public function download(LotPhysique $lot)
    {
        if ($lot->user_id !== Auth::id()) {
            abort(403);
        }

        if (! $lot->estTransmis) {
            return back()->with('error', 'Ce lot n\'a pas encore été transmis par le super admin.');
        }

        $max = config('app.max_downloads');

        if ($lot->download_count >= $max) {
            return back()->with('error', 'Limite de téléchargements atteinte ('.$max.' maximum). Contactez le support si nécessaire.');
        }

        $tickets = $lot->tickets()->where('annule', false)->orderBy('code_unique')->get();

        if ($tickets->isEmpty()) {
            return back()->with('error', 'Aucun ticket valide à imprimer dans ce lot.');
        }

        // Si un template est configuré, génère le PDF avec le template
        if ($lot->aUnTemplate()) {
            $pdf = LotPhysiqueTemplatePdfService::generer($lot, $tickets);
        } else {
            $pdf = LotPhysiquePdfService::generer($lot, $tickets);
        }

        $lot->increment('download_count');

        return $pdf->download('Planche-'.$lot->nom.'-'.$lot->evenement?->titre.'.pdf');
    }

    // Supprime un lot de tickets physiques et ses tickets (jamais si des tickets ont été scannés)
    public function destroy(LotPhysique $lot): RedirectResponse
    {
        if ($lot->user_id !== Auth::id()) {
            abort(403);
        }

        // Lot « demande » déjà payé : la suppression relève du super admin (commission réglée)
        if ($lot->estUneDemande() && $lot->statut !== LotPhysique::STATUT_ATTENTE_PAIEMENT) {
            return redirect()->route('admin.lots-physiques.index')
                ->with('error', 'Cette demande est payée : elle ne peut plus être supprimée. Contactez le support PaxEvent si nécessaire.');
        }

        $nbScannes = $lot->tickets()->where('utilise', true)->count();
        if ($nbScannes > 0) {
            return redirect()->route('admin.lots-physiques.index')
                ->with('error', 'Impossible de supprimer ce lot : des tickets ont déjà été scannés à l\'entrée.');
        }

        DB::transaction(function () use ($lot) {
            Ticket::where('lot_physique_id', $lot->id)->delete();
            $lot->delete();
        });

        return redirect()->route('admin.lots-physiques.index')
            ->with('success', 'Lot supprimé.');
    }

    // ─── TEMPLATE : configuration du design physique ────────────────────────

    // Page step 2 : upload du template image + positionnement du QR code
    public function showTemplate(LotPhysique $lot)
    {
        if ($lot->user_id !== Auth::id()) {
            abort(403);
        }

        if ($lot->estUneDemande()) {
            return redirect()->route('admin.lots-physiques.index')
                ->with('error', 'Le QR code de ce lot est positionné par l\'équipe PaxEvent.');
        }

        $tickets = $lot->tickets()->where('annule', false)->count();
        $format = $lot->formatDetails();
        $qrSize = $lot->qr_size ?? $format['qr_defaut'];
        $qrX = $lot->qr_x ?? round(($format['largeur'] - $qrSize) / 2);
        $qrY = $lot->qr_y ?? round(($format['hauteur'] - $qrSize) / 2);
        $formats = array_map(fn ($f) => $f['label'], LotPhysique::FORMATS);
        $formats[LotPhysique::FORMAT_CUSTOM] = 'Personnalisé (dimensions sur mesure)';

        return view('admin.lots-physiques.template', compact('lot', 'tickets', 'format', 'qrX', 'qrY', 'qrSize', 'formats'))->with('zoom', $lot->template_zoom ?? 100);
    }

    // Sauvegarde du template image + coordonnées QR
    public function saveTemplate(Request $request, LotPhysique $lot)
    {
        if ($lot->user_id !== Auth::id()) {
            abort(403);
        }

        if ($lot->estUneDemande()) {
            return back()->with('error', 'Le QR code de ce lot est positionné par l\'équipe PaxEvent.');
        }

        if ($erreur = UploadError::pour($request, 'template_image')) {
            return back()->withInput()->withErrors(['template_image' => $erreur]);
        }

        $hasTemplate = $lot->template_path && Storage::disk('public')->exists($lot->template_path);

        $rules = [
            'format' => ['required', 'in:s1,s2,v1,v2,a5,custom'],
            'largeur_personnalisee' => 'nullable|numeric|min:1|max:10000',
            'hauteur_personnalisee' => 'nullable|numeric|min:1|max:10000',
            'qr_x' => 'nullable|numeric|min:0',
            'qr_y' => 'nullable|numeric|min:0',
            'qr_size' => ['nullable', 'numeric', 'min:1', 'max:10000'],
            'supprimer_template' => 'nullable|boolean',
            'template_zoom' => 'nullable|numeric|min:70|max:150',
        ];
        $rules['template_image'] = $request->hasFile('template_image')
            ? ['image', 'mimes:png', 'max:10240']
            : ($hasTemplate ? ['nullable'] : ['required']);

        $validated = $request->validate($rules, [
            'format.required' => 'Veuillez choisir un format.',
            'format.in' => 'Format invalide.',
            'template_image.required' => 'Veuillez importer une image de template.',
            'template_image.image' => 'Le fichier doit être une image.',
            'template_image.mimes' => 'Format accepté : PNG uniquement.',
            'template_image.max' => 'L\'image ne doit pas dépasser 10 Mo.',
            'qr_x.numeric' => 'Position X du QR code invalide.',
            'qr_y.numeric' => 'Position Y du QR code invalide.',
            'qr_size.numeric' => 'Taille du QR code invalide.',
            'qr_size.min' => 'La taille du QR doit être d\'au moins 1 mm.',
            'qr_size.max' => 'La taille du QR ne doit pas dépasser 10000 mm.',
            'template_zoom.numeric' => 'Zoom de l\'image invalide.',
            'template_zoom.min' => 'Le zoom ne peut pas être inférieur à 70 %.',
            'template_zoom.max' => 'Le zoom ne peut pas dépasser 150 %.',
            'largeur_personnalisee.numeric' => 'La largeur personnalisée doit être un nombre.',
            'hauteur_personnalisee.numeric' => 'La hauteur personnalisée doit être un nombre.',
            'largeur_personnalisee.min' => 'La largeur doit être d\'au moins 1 mm.',
            'hauteur_personnalisee.min' => 'La hauteur doit être d\'au moins 1 mm.',
        ]);

        if ($validated['format'] === LotPhysique::FORMAT_CUSTOM) {
            if ($request->filled('largeur_personnalisee')) {
                $request->merge(['largeur_personnalisee' => round((float) $request->input('largeur_personnalisee'), 1)]);
            }
            if ($request->filled('hauteur_personnalisee')) {
                $request->merge(['hauteur_personnalisee' => round((float) $request->input('hauteur_personnalisee'), 1)]);
            }

            $validated = array_merge($validated, $request->validate([
                'largeur_personnalisee' => 'required|numeric|min:1|max:10000',
                'hauteur_personnalisee' => 'required|numeric|min:1|max:10000',
            ], [
                'largeur_personnalisee.required' => 'Les dimensions du ticket personnalisé n\'ont pas été calculées depuis l\'image. Veuillez réimporter l\'image du ticket.',
                'hauteur_personnalisee.required' => 'Les dimensions du ticket personnalisé n\'ont pas été calculées depuis l\'image. Veuillez réimporter l\'image du ticket.',
                'largeur_personnalisee.numeric' => 'La largeur personnalisée doit être un nombre.',
                'hauteur_personnalisee.numeric' => 'La hauteur personnalisée doit être un nombre.',
                'largeur_personnalisee.min' => 'La largeur doit être d\'au moins 1 mm.',
                'hauteur_personnalisee.min' => 'La hauteur doit être d\'au moins 1 mm.',
            ]));

            $formatDef = LotPhysique::formatPersonnalise((float) $validated['largeur_personnalisee'], (float) $validated['hauteur_personnalisee']);
            if ($formatDef === null) {
                return back()->withInput()->with('error', 'Dimensions personnalisées invalides.');
            }
        } else {
            $formatDef = LotPhysique::FORMATS[$validated['format']];
        }

        // Suppression du template demandée (croix ✕)
        if ($request->boolean('supprimer_template')) {
            if ($lot->template_path && Storage::disk('public')->exists($lot->template_path)) {
                Storage::disk('public')->delete($lot->template_path);
            }
            $lot->template_path = null;
        } elseif ($request->hasFile('template_image')) {
            if ($lot->template_path && Storage::disk('public')->exists($lot->template_path)) {
                Storage::disk('public')->delete($lot->template_path);
            }

            $file = $request->file('template_image');
            $filename = 'lot-templates/'.$lot->id.'_'.time().'.'.$file->getClientOriginalExtension();
            $file->storeAs('', $filename, 'public');
            $lot->template_path = $filename;
        }

        // QR code : garde les valeurs du lot si le format n'a pas changé, sinon défauts du format
        $qrSize = array_key_exists('qr_size', $validated) && $validated['qr_size'] !== null ? (float) $validated['qr_size'] : ($lot->qr_size ?? $formatDef['qr_defaut']);
        $qrX = array_key_exists('qr_x', $validated) && $validated['qr_x'] !== null ? (int) $validated['qr_x'] : ($lot->qr_x ?? round(($formatDef['largeur'] - $qrSize) / 2));
        $qrY = array_key_exists('qr_y', $validated) && $validated['qr_y'] !== null ? (int) $validated['qr_y'] : ($lot->qr_y ?? round(($formatDef['hauteur'] - $qrSize) / 2));
        $zoom = isset($validated['template_zoom']) ? (int) $validated['template_zoom'] : ($lot->template_zoom ?? 100);

        $lot->update([
            'format' => $validated['format'],
            'largeur_personnalisee' => $validated['format'] === LotPhysique::FORMAT_CUSTOM ? (int) round((float) $validated['largeur_personnalisee']) : null,
            'hauteur_personnalisee' => $validated['format'] === LotPhysique::FORMAT_CUSTOM ? (int) round((float) $validated['hauteur_personnalisee']) : null,
            'qr_x' => $qrX,
            'qr_y' => $qrY,
            'qr_size' => $qrSize,
            'template_zoom' => $zoom,
        ]);

        return back()->with('success', 'Template enregistré. Vous pouvez maintenant télécharger votre planche.');
    }

    // Aperçu PDF d'un ticket composité (template + QR)
    public function previewTemplate(LotPhysique $lot)
    {
        if ($lot->user_id !== Auth::id()) {
            abort(403);
        }

        if ($lot->estUneDemande()) {
            abort(403);
        }

        $ticket = $lot->tickets()->where('annule', false)->first();

        // Sans ticket valide, on compose l'aperçu sur un code d'exemple
        if (! $ticket) {
            $ticket = new Ticket;
            $ticket->code_unique = 'PAX-XXXXX';
        }

        try {
            return LotPhysiqueTemplatePdfService::apercuTicket($lot, $ticket);
        } catch (\Throwable $e) {
            FacadesLog::error('Aperçu template lot physique échoué : '.$e->getMessage());

            abort(500, 'Erreur lors de la génération de l\'aperçu.');
        }
    }
}
