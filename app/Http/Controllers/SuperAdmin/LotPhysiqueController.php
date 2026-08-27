<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Mail\LotPhysiqueTransmis;
use App\Models\Evenement;
use App\Models\Log;
use App\Models\LotPhysique;
use App\Models\Message;
use App\Models\Tarif;
use App\Models\Ticket;
use App\Models\User;
use App\Services\LotPhysiquePdfService;
use App\Services\LotPhysiqueTemplatePdfService;
use App\Services\QrCodeService;
use App\Support\PerPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log as FacadesLog;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LotPhysiqueController extends Controller
{
    // Liste des lots de tickets physiques
    public function index()
    {
        $lots = LotPhysique::with('evenement', 'user', 'tarif')
            ->withCount(['tickets as nb_tickets'])
            ->withCount(['tickets as nb_annules' => fn ($q) => $q->where('annule', true)])
            ->withCount(['tickets as nb_scannes' => fn ($q) => $q->where('utilise', true)])
            ->orderByDesc('created_at')
            ->paginate(PerPage::resolve());

        return view('superadmin.lots-physiques.index', compact('lots'));
    }

    // Formulaire de création d'un lot
    public function create()
    {
        $organisateurs = User::where('role', 'admin')->orderBy('nom')->get();

        return view('superadmin.lots-physiques.create', compact('organisateurs'));
    }

    // Événements d'un organisateur (sélecteur dynamique)
    public function getEvenements(Request $request)
    {
        $request->validate(['user_id' => 'required|exists:users,id'
        ],[
            'user_id.required' => 'Veuillez sélectionner un organisateur.',
            'user_id.exists' => 'Organisateur invalide.',
        ]
        );

        $evenements = Evenement::where('user_id', $request->user_id)
            ->where(fn ($q) => $q->whereNull('date_event')->orWhere('date_event', '>=', now()))
            ->orderBy('date_event', 'asc')
            ->get(['id', 'titre', 'date_event']);

        return response()->json(['evenements' => $evenements]);
    }

    // Tarifs d'un événement (sélecteur dynamique)
    public function getTarifs(Request $request)
    {
        $request->validate([
            'evenement_id' => 'required|exists:evenement,id'
        ], [
            'evenement_id.required' => 'Veuillez sélectionner un événement.',
            'evenement_id.exists' => 'Événement invalide.',
        ]);

        $evenement = Evenement::findOrFail($request->evenement_id);
        $tarifs = $evenement->tarifs()->where('statut', 'actif')->get(['id', 'nom', 'prix']);

        return response()->json([
            'tarifs' => $tarifs,
            'gratuit' => (bool) $evenement->gratuit,
            'commission' => $evenement->commissionEffective(),
        ]);
    }

    // Génère un lot : lot + tickets physiques (PAX-XXXXX)
    public function store(Request $request)
    {
        $rules = [
            'user_id' => 'required|exists:users,id',
            'evenement_id' => 'required|exists:evenement,id',
            'nom' => 'required|string|max:100',
            'quantite' => 'required|integer|min:1|max:1000',
            'commission_pourcentage' => 'nullable|numeric|min:0|max:100',
        ];
        $messages = [
            'user_id.required' => 'Veuillez sélectionner un organisateur.',
            'evenement_id.required' => 'Veuillez sélectionner un événement.',
            'nom.required' => 'Le nom du lot est obligatoire.',
            'quantite.required' => 'La quantité est obligatoire.',
            'quantite.min' => 'La quantité doit être d\'au moins 1.',
            'quantite.max' => 'La quantité ne doit pas dépasser 1000.',
        ];

        $validated = $request->validate($rules, $messages);

        $evenement = Evenement::findOrFail($validated['evenement_id']);

        if ($evenement->user_id !== (int) $validated['user_id']) {
            return back()->withInput()->with('error', 'Cet événement n\'appartient pas à l\'organisateur sélectionné.');
        }

        if (! $evenement->gratuit) {
            $request->validate(
                ['tarif_id' => 'required|exists:tarifs,id'],
                ['tarif_id.required' => 'Veuillez sélectionner un tarif.']
            );
            $tarif = Tarif::where('evenement_id', $evenement->id)->findOrFail($request->tarif_id);
        } else {
            $tarif = $evenement->tarifs()->where('statut', 'actif')->first();
        }

        if (! $tarif) {
            return back()->withInput()->with('error', 'Aucun tarif actif pour cet événement.');
        }

        // Prix du ticket physique = prix du tarif choisi ; commission % facultative (sinon taux événement)
        $commission = $validated['commission_pourcentage'] !== null && $validated['commission_pourcentage'] !== ''
            ? (float) $validated['commission_pourcentage']
            : null;

        $lot = DB::transaction(function () use ($validated, $evenement, $tarif, $commission) {
            $lot = LotPhysique::create([
                'user_id' => $validated['user_id'],
                'evenement_id' => $evenement->id,
                'tarif_id' => $tarif->id,
                'commission_pourcentage' => $commission,
                'nom' => $validated['nom'],
                'quantite' => $validated['quantite'],
                'statut' => 'genere',
                'download_count' => 0,
            ]);

            for ($i = 0; $i < $validated['quantite']; $i++) {
                $ticket = Ticket::create([
                    'evenement_id' => $evenement->id,
                    'tarif_id' => $tarif->id,
                    'lot_physique_id' => $lot->id,
                    'source' => 'physique',
                    'code_unique' => 'TMP',
                    'qr_signature' => hash_hmac('sha256', Str::random(32), config('app.key') ?? 'fallback'),
                    'email_acheteur' => null,
                    'telephone_acheteur' => null,
                    'nom_acheteur' => null,
                    'nom_tarif' => $tarif->nom,
                    'montant' => (float) $tarif->prix,
                    'montant_reduction' => 0,
                    'quantite' => 1,
                    'statut_paiement' => 'payé',
                    'methode_paiement' => 'especes',
                    'type_paiement' => 'especes',
                    'transaction_id' => 'PHYS-'.strtoupper(Str::random(8)),
                    'utilise' => false,
                    'date_achat' => now(),
                ]);
                $ticket->update([
                    'code_unique' => Ticket::genererCodeSecurise(),
                ]);
            }

            Log::create([
                'type_operation' => 'lot_physique',
                'ticket_id' => null,
                'details' => [
                    'lot_id' => $lot->id,
                    'user_id' => $validated['user_id'],
                    'evenement_id' => $evenement->id,
                    'quantite' => $validated['quantite'],
                    'commission_pourcentage' => $commission,
                ],
                'ip' => request()->ip(),
            ]);

            return $lot;
        });

        return redirect()
            ->route('superadmin.tickets-physiques.voir', $lot)
            ->with('success', "Lot « {$lot->nom } » de {$lot->quantite} ticket(s) généré avec succès.");
    }

    // Télécharge la planche PDF d'un lot (côté super admin, sans limite)
    public function telechargerPlanche(LotPhysique $lot)
    {
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

        return $pdf->download('Planche-'.$lot->nom.'.pdf');
    }

    // Télécharge un PDF unique regroupant toutes les planches (côté super admin)
    public function telechargerPlanches()
    {
        $lots = LotPhysique::with('evenement', 'tarif')
            ->whereHas('tickets', fn ($q) => $q->where('annule', false))
            ->orderByDesc('created_at')
            ->get();

        if ($lots->isEmpty()) {
            return back()->with('error', 'Aucun lot avec des tickets valides à imprimer.');
        }

        $pdf = LotPhysiquePdfService::genererPlusieurs($lots);

        return $pdf->download('Planches-tickets-physiques.pdf');
    }

    // Détail d'un lot : tickets avec codes et QR
    public function show(LotPhysique $lot)
    {
        $lot->load('evenement', 'user', 'tarif');

        $tickets = $lot->tickets()->orderBy('code_unique')->get();

        $qrs = $tickets->mapWithKeys(fn (Ticket $t) => [
            $t->id => QrCodeService::generateDataUri($t->code_unique, 120),
        ]);

        return view('superadmin.lots-physiques.show', compact('lot', 'tickets', 'qrs'));
    }

    // Transmet le lot à l'organisateur (notification + email + note optionnelle)
    public function transmettre(Request $request, LotPhysique $lot)
    {
        if ($lot->estTransmis) {
            return back()->with('error', 'Ce lot a déjà été transmis à l\'organisateur.');
        }

        $note = trim((string) $request->input('note'));
        $emailDest = trim((string) $request->input('email'));
        if ($emailDest !== '' && ! filter_var($emailDest, FILTER_VALIDATE_EMAIL)) {
            return back()->with('error', 'L\'adresse email saisie n\'est pas valide.');
        }
        if ($emailDest === '') {
            $emailDest = $lot->user?->email;
        }

        $corps = "Bonjour {$lot->user?->nom},\n\n"
            ."Un lot de tickets physiques est disponible pour votre événement « {$lot->evenement?->titre} ».\n\n"
            ."Lot : {$lot->nom} ({$lot->quantite} tickets)\n\n"
            .'Connectez-vous à votre espace organisateur, rubrique « Vente physique », pour télécharger la planche de QR codes (3 téléchargements maximum).';
        if ($note !== '') {
            $corps .= "\n\nNote du super admin :\n{$note}";
        }

        $lot->update(['statut' => 'transmis', 'transmis_at' => now()]);

        Message::create([
            'user_id' => $lot->user_id,
            'evenement_id' => $lot->evenement_id,
            'nom_complet' => $lot->user?->nom,
            'email' => $emailDest,
            'objet' => 'Tickets physiques disponibles',
            'message' => $corps,
            'lu' => false,
        ]);

        try {
            Mail::to($emailDest)->send(new LotPhysiqueTransmis($lot, $note));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Lot physique - Erreur email transmission : '.$e->getMessage());
        }

        Log::create([
            'type_operation' => 'lot_physique_transmis',
            'ticket_id' => null,
            'details' => ['lot_id' => $lot->id, 'user_id' => $lot->user_id],
            'ip' => request()->ip(),
        ]);

        return back()->with('success', "Le lot « {$lot->nom} » a été transmis à l'organisateur.");
    }

    // Annule un ticket du lot (erreur d'impression, billet perdu…)
    public function annulerTicket(LotPhysique $lot, Ticket $ticket)
    {
        if ($ticket->lot_physique_id !== $lot->id) {
            return back()->with('error', 'Ce ticket n\'appartient pas à ce lot.');
        }

        if ($ticket->annule) {
            return back()->with('error', 'Ce ticket est déjà annulé.');
        }

        if ($ticket->utilise) {
            return back()->with('error', 'Impossible d\'annuler un ticket déjà scanné.');
        }

        $ticket->update(['annule' => true]);

        Log::create([
            'type_operation' => 'lot_physique_annulation',
            'ticket_id' => $ticket->id,
            'details' => ['lot_id' => $lot->id, 'code' => $ticket->code_unique],
            'ip' => request()->ip(),
        ]);

        return back()->with('success', "Le ticket {$ticket->code_unique} a été annulé et ne sera plus scannable.");
    }

    // Action en masse sur plusieurs tickets du lot (annuler ou supprimer)
    public function actionMasse(Request $request, LotPhysique $lot)
    {
        $validated = $request->validate([
            'action' => 'required|in:annuler,supprimer',
            'tickets' => 'required|array|min:1',
            'tickets.*' => 'integer|exists:ticket,id',
        ],
        [
            'action.required' => 'Veuillez sélectionner une action.',
            'action.in' => 'Action invalide.',
            'tickets.required' => 'Veuillez sélectionner au moins un ticket.',
            'tickets.array' => 'Tickets invalides.',
            'tickets.min' => 'Veuillez sélectionner au moins un ticket.',
            'tickets.*.exists' => 'Ticket invalide.',
        ]);

        $tickets = $lot->tickets()
            ->whereIn('id', $validated['tickets'])
            ->get();

        if ($tickets->isEmpty()) {
            return back()->with('error', 'Aucun ticket sélectionné.');
        }

        $traites = 0;
        $ignores = 0;

        DB::transaction(function () use ($tickets, $validated, &$traites, &$ignores, $lot) {
            foreach ($tickets as $ticket) {
                // Un ticket déjà scanné ne peut ni être annulé ni supprimé
                if ($ticket->utilise) {
                    $ignores++;

                    continue;
                }

                if ($validated['action'] === 'annuler') {
                    if ($ticket->annule) {
                        $ignores++;

                        continue;
                    }
                    $ticket->update(['annule' => true]);

                    Log::create([
                        'type_operation' => 'lot_physique_annulation',
                        'ticket_id' => $ticket->id,
                        'details' => ['lot_id' => $lot->id, 'code' => $ticket->code_unique, 'action' => $validated['action']],
                        'ip' => request()->ip(),
                    ]);
                } else {
                    Log::create([
                        'type_operation' => 'lot_physique_ticket_supprime',
                        'ticket_id' => $ticket->id,
                        'details' => ['lot_id' => $lot->id, 'code' => $ticket->code_unique, 'action' => $validated['action']],
                        'ip' => request()->ip(),
                    ]);
                    $ticket->delete();
                }

                $traites++;
            }
        });

        $libelle = $validated['action'] === 'annuler' ? 'annulé' : 'supprimé';
        $message = "{$traites} ticket(s) {$libelle}(s).";
        if ($ignores > 0) {
            $message .= " {$ignores} ticket(s) ignoré(s) (déjà scanné(s) ou déjà traité(s)).";
        }

        return back()->with($traites > 0 ? 'success' : 'error', $message);
    }

    // Supprime un lot (seulement s'il n'a pas été transmis)
    public function destroy(LotPhysique $lot)
    {
        if ($lot->estTransmis) {
            return back()->with('error', 'Un lot transmis ne peut pas être supprimé.');
        }

        DB::transaction(function () use ($lot) {
            $lot->tickets()->delete();
            $lot->delete();
        });

        Log::create([
            'type_operation' => 'lot_physique_supprime',
            'ticket_id' => null,
            'details' => ['lot_id' => $lot->id, 'evenement_id' => $lot->evenement_id],
            'ip' => request()->ip(),
        ]);

        return redirect()
            ->route('superadmin.tickets-physiques')
            ->with('success', 'Lot supprimé.');
    }

    // ─── TEMPLATE : configuration du design physique (superadmin) ───────────

    public function showTemplate(LotPhysique $lot)
    {
        $tickets = $lot->tickets()->where('annule', false)->count();
        $format = $lot->formatDetails();
        $qrSize = $lot->qr_size ?? $format['qr_defaut'];
        $qrX = $lot->qr_x ?? round(($format['largeur'] - $qrSize) / 2);
        $qrY = $lot->qr_y ?? round(($format['hauteur'] - $qrSize) / 2);
        $formats = array_map(fn ($f) => $f['label'], LotPhysique::FORMATS);

        return view('superadmin.lots-physiques.template', compact('lot', 'tickets', 'format', 'qrX', 'qrY', 'qrSize', 'formats'));
    }

    public function saveTemplate(Request $request, LotPhysique $lot)
    {
        $hasTemplate = $lot->template_path && Storage::disk('public')->exists($lot->template_path);

        $rules = [
            'format' => ['required', 'in:s1,s2,v1,v2'],
            'qr_x' => 'nullable|numeric|min:0',
            'qr_y' => 'nullable|numeric|min:0',
            'qr_size' => 'nullable|numeric|min:20|max:80',
            'supprimer_template' => 'nullable|boolean',
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
            'qr_size.min' => 'La taille du QR doit être d\'au moins 20 mm.',
            'qr_size.max' => 'La taille du QR ne doit pas dépasser 80 mm.',
        ]);

        $formatDef = LotPhysique::FORMATS[$validated['format']];

        // Suppression du template demandée (croix ✕)
        if ($request->boolean('supprimer_template')) {
            if ($lot->template_path && Storage::disk('public')->exists($lot->template_path)) {
                Storage::disk('public')->delete($lot->template_path);
            }
            $lot->template_path = null;
        } elseif ($request->hasFile('template_image')) {
            // Vérifie le ratio de l'image vs format choisi (tolérance 1 %)
            $ratioAttendu = $formatDef['largeur'] / $formatDef['hauteur'];
            $image = [0, 0];
            $test = getimagesize($request->file('template_image')->getRealPath());
            if ($test !== false) {
                $image = [$test[0], $test[1]];
                $ratioReel = $image[0] / max($image[1], 1);
                if (abs($ratioReel - $ratioAttendu) / $ratioAttendu > 0.01) {
                    return back()
                        ->withErrors(['template_image' => "Cette image ne respecte pas le format « {$formatDef['label']} ». Ratio attendu : {$formatDef['largeur']}×{$formatDef['hauteur']} mm. Redimensionnez votre image."])
                        ->withInput();
                }
            }

            if ($lot->template_path && Storage::disk('public')->exists($lot->template_path)) {
                Storage::disk('public')->delete($lot->template_path);
            }

            $file = $request->file('template_image');
            $filename = 'lot-templates/'.$lot->id.'_'.time().'.'.$file->getClientOriginalExtension();
            $file->storeAs('', $filename, 'public');
            $lot->template_path = $filename;
        }

        // QR code : garde les valeurs du lot si le format n'a pas changé, sinon défauts du format
        $qrSize = isset($validated['qr_size']) ? (int) $validated['qr_size'] : ($lot->qr_size ?? $formatDef['qr_defaut']);
        $qrX = isset($validated['qr_x']) ? (int) $validated['qr_x'] : ($lot->qr_x ?? round(($formatDef['largeur'] - $qrSize) / 2));
        $qrY = isset($validated['qr_y']) ? (int) $validated['qr_y'] : ($lot->qr_y ?? round(($formatDef['hauteur'] - $qrSize) / 2));

        $lot->update([
            'format' => $validated['format'],
            'qr_x' => $qrX,
            'qr_y' => $qrY,
            'qr_size' => $qrSize,
        ]);

        return back()->with('success', 'Template enregistré. Le prochain téléchargement utilisera ce design.');
    }

    public function previewTemplate(LotPhysique $lot)
    {
        $ticket = $lot->tickets()->where('annule', false)->first();

        if (! $ticket) {
            return response()->json(['error' => 'Aucun ticket valide.'], 404);
        }

        try {
            $pdfContent = LotPhysiqueTemplatePdfService::apercuTicket($lot, $ticket);

            return response()->json(['pdf' => $pdfContent]);
        } catch (\Exception $e) {
            FacadesLog::error('Aperçu template lot physique échoué : '.$e->getMessage());

            return response()->json(['error' => 'Erreur lors de la génération de l\'aperçu.'], 500);
        }
    }
}
