<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\LotPhysique;
use App\Models\Evenement;
use App\Models\Log;
use App\Models\Tarif;
use App\Models\Ticket;
use App\Services\LotPhysiquePdfService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * QR codes pour des événements non enregistrés sur la plateforme.
 * Le super admin choisit un événement interne, indique la quantité par tarif, puis importe
 * le visuel et positionne le QR sur la page de template commune aux lots physiques.
 * Les anciens lots « sans événement » (codes non scannables) restent consultables.
 */
class QrExterneController extends Controller
{
    public function index()
    {
        $lots = LotPhysique::query()
            ->where(fn ($q) => $q->where('externe', true)
                ->orWhereHas('evenement', fn ($e) => $e->where('interne', true)))
            ->with('evenement', 'tarif')
            ->withCount('codesExternes')
            ->withCount(['tickets as nb_tickets' => fn ($q) => $q->where('annule', false)])
            ->latest()
            ->paginate(20);

        // Événements internes à venir (ou sans date passée) et leurs tarifs actifs
        $evenements = Evenement::where('interne', true)
            ->where('statut', '!=', 'annulé')
            ->with(['tarifs' => fn ($q) => $q->where('statut', 'actif')->orderBy('id')])
            ->orderByDesc('date_event')
            ->get();

        return view('superadmin.qr-externes.index', compact('lots', 'evenements'));
    }

    // Génère, pour un événement interne, un lot de tickets physiques par tarif.
    // Les tickets sont de vrais billets (scannables) mais sans montant : ils
    // n'entrent ni dans le chiffre d'affaires ni dans les commissions.
    public function store(Request $request)
    {
        $validated = $request->validate([
            'evenement_id' => 'required|integer',
            'quantites' => 'required|array',
            'quantites.*' => 'nullable|integer|min:0|max:1000',
        ], [
            'evenement_id.required' => 'Veuillez sélectionner un événement.',
            'quantites.required' => 'Indiquez au moins une quantité.',
            'quantites.*.max' => 'Maximum 1000 QR codes par tarif.',
            'quantites.*.min' => 'Quantité invalide.',
            'quantites.*.integer' => 'Quantité invalide.',
        ]);

        $evenement = Evenement::where('interne', true)->find($validated['evenement_id']);
        if (! $evenement) {
            return back()->withInput()->withErrors(['evenement_id' => 'Événement interne introuvable.']);
        }

        $lignes = [];
        foreach ($validated['quantites'] as $tarifId => $quantite) {
            $quantite = (int) $quantite;
            if ($quantite <= 0) {
                continue;
            }
            $tarif = $evenement->tarifs()->where('statut', 'actif')->find($tarifId);
            if (! $tarif) {
                return back()->withInput()->with('error', 'Un des tarifs choisis n\'existe plus.');
            }
            $lignes[] = [$tarif, $quantite];
        }

        if (empty($lignes)) {
            return back()->withInput()->withErrors(['quantites' => 'Indiquez au moins une quantité.']);
        }

        $lots = [];
        $derniereErreur = null;

        // Réessaie en cas de collision de code (1062) ou de verrou (1205/1213)
        for ($essai = 1; $essai <= 3; $essai++) {
            try {
                $lots = DB::transaction(function () use ($evenement, $lignes) {
                    $crees = [];
                    foreach ($lignes as [$tarif, $quantite]) {
                        $crees[] = $this->creerLot($evenement, $tarif, $quantite);
                    }

                    return $crees;
                });
                break;
            } catch (QueryException $e) {
                $derniereErreur = $e;
                $lots = [];
                if (! in_array((int) ($e->errorInfo[1] ?? -1), [1205, 1213, 1062], true)) {
                    throw $e;
                }
                usleep(150_000 * $essai);
            }
        }

        if (empty($lots)) {
            throw $derniereErreur;
        }

        $total = collect($lignes)->sum(fn ($l) => $l[1]);
        $message = "{$total} QR code(s) générés. Importez maintenant le visuel du ticket et placez le QR code.";
        if (count($lots) > 1) {
            $message .= ' Un lot a été créé par tarif : retrouvez les autres dans « QR hors plateforme ».';
        }

        return redirect()
            ->route('superadmin.tickets-physiques.template', $lots[0])
            ->with('success', $message);
    }

    private function creerLot(Evenement $evenement, Tarif $tarif, int $quantite): LotPhysique
    {
        $lot = LotPhysique::create([
            'user_id' => $evenement->user_id,
            'evenement_id' => $evenement->id,
            'tarif_id' => $tarif->id,
            'commission_pourcentage' => 0,
            'nom' => Str::limit($tarif->nom.' — '.$evenement->titre, 90, ''),
            'quantite' => $quantite,
            'statut' => LotPhysique::STATUT_TRANSMIS,
            'transmis_at' => now(),
            'auto_genere' => true,
            'download_count' => 0,
            'format' => 's1',
        ]);

        $codes = [];
        while (count($codes) < $quantite) {
            $code = Ticket::genererCodeSecurise();
            $codes[$code] = true;
        }

        $maintenant = now();
        DB::table('ticket')->insert(array_map(fn ($code) => [
            'evenement_id' => $evenement->id,
            'tarif_id' => $tarif->id,
            'lot_physique_id' => $lot->id,
            'source' => 'physique',
            'code_unique' => $code,
            'qr_signature' => hash_hmac('sha256', Str::random(32), config('app.key') ?? 'fallback'),
            'email_acheteur' => null,
            'telephone_acheteur' => null,
            'nom_acheteur' => null,
            'nom_tarif' => $tarif->nom,
            'montant' => 0,
            'montant_reduction' => 0,
            'quantite' => 1,
            'statut_paiement' => 'payé',
            'methode_paiement' => 'especes',
            'type_paiement' => 'especes',
            'transaction_id' => 'INT-'.strtoupper(Str::random(8)),
            'utilise' => false,
            'date_achat' => $maintenant,
            'created_at' => $maintenant,
            'updated_at' => $maintenant,
        ], array_keys($codes)));

        Log::create([
            'type_operation' => 'lot_evenement_interne',
            'ticket_id' => null,
            'details' => ['lot_id' => $lot->id, 'evenement_id' => $evenement->id, 'tarif_id' => $tarif->id, 'quantite' => $quantite],
            'ip' => request()->ip(),
        ]);

        return $lot;
    }

    // PDF des QR codes seuls (grille A4, sans visuel), comme la planche des QR générés en ligne
    public function telechargerQr(LotPhysique $lot)
    {
        abort_unless($lot->externe || $lot->evenement?->interne, 404);

        $codes = $lot->ticketsImprimables();
        if ($codes->isEmpty()) {
            return back()->with('error', 'Aucun QR code dans ce lot.');
        }

        $nom = str_replace(['/', '\\'], '-', $lot->titreEvenement() ?? 'lot-'.$lot->id);

        return LotPhysiquePdfService::generer($lot, $codes)->download('QR-codes-'.$nom.'.pdf');
    }

    public function destroy(LotPhysique $lot)
    {
        abort_unless($lot->externe || $lot->evenement?->interne, 404);

        if ($lot->tickets()->where('utilise', true)->exists()) {
            return back()->with('error', 'Impossible de supprimer ce lot : des tickets ont déjà été scannés.');
        }
        Ticket::where('lot_physique_id', $lot->id)->delete();

        if ($lot->template_path) {
            Storage::disk('public')->delete($lot->template_path);
        }
        $lot->delete();

        return redirect()->route('superadmin.qr-externes')->with('success', 'Lot supprimé.');
    }
}
