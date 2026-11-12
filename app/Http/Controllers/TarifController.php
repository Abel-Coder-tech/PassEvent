<?php

namespace App\Http\Controllers;

use App\Models\Tarif;
use App\Models\Evenement;
use App\Models\User;
use App\Models\DemandeModificationTarif;
use App\Mail\TarifDemandeNotification;
use App\Mail\ListeAttentePlacesDisponibles;
use App\Models\EventWaitlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TarifController extends Controller
{
    // Liste les tarifs d'un événement
    public function index(Evenement $evenement)
    {
        abort_if($evenement->user_id !== Auth::id(), 403); // Vérification de propriété
        $tarifs = $evenement->tarifs()->with('jour')->get();
        $jours = $evenement->dates()->get();

        // Liste d'attente par tarif : personnes inscrites et places demandées
        $listeAttente = EventWaitlist::where('evenement_id', $evenement->id)
            ->where('statut', 'en_attente')
            ->selectRaw('tarif_id, COUNT(*) as personnes, SUM(quantite) as places')
            ->groupBy('tarif_id')
            ->get()
            ->keyBy('tarif_id');

        return view('tarifs.index', compact('evenement', 'tarifs', 'jours', 'listeAttente'));
    }

    // Prévient en une fois tous les inscrits de la liste d'attente d'un tarif
    // (après ajout de places) : un email avec le lien de l'événement.
    public function notifierListeAttente(Evenement $evenement, Tarif $tarif)
    {
        abort_if($evenement->user_id !== Auth::id(), 403);
        abort_if((int) $tarif->evenement_id !== (int) $evenement->id, 404);

        $demandes = EventWaitlist::where('tarif_id', $tarif->id)
            ->where('statut', 'en_attente')
            ->get();

        if ($demandes->isEmpty()) {
            return back()->with('error', "Personne n'attend sur la liste de « {$tarif->nom} ».");
        }

        $restantes = max(0, min(
            $evenement->placesRestantesPourTarif($tarif),
            $tarif->quantite_disponible === null ? PHP_INT_MAX : (int) $tarif->quantite_disponible - (int) $tarif->quantite_vendue
        ));
        if ($restantes <= 0) {
            return back()->with('error', "« {$tarif->nom} » est toujours complet : ajoutez d'abord des places (bouton Modifier), puis envoyez la notification.");
        }

        foreach ($demandes as $demande) {
            Mail::to($demande->email_acheteur)->queue(new ListeAttentePlacesDisponibles($demande, $evenement, $tarif));
            $demande->update(['statut' => 'place_offerte', 'place_offerte_le' => now()]);
        }

        return back()->with('success', "{$demandes->count()} personne(s) prévenue(s) pour « {$tarif->nom} ». {$restantes} place(s) disponible(s) : premier arrivé, premier servi.");
    }

    // Affiche le formulaire de création d'un tarif
    public function create(Evenement $evenement)
    {
        abort_if($evenement->user_id !== Auth::id(), 403); // Vérification de propriété
        $jours = $evenement->dates()->get();

        return view('tarifs.create', compact('evenement', 'jours'));
    }

    // Crée un nouveau tarif pour un événement
    public function store(Request $request, Evenement $evenement)
    {
        abort_if($evenement->user_id !== Auth::id(), 403);

        $nbTarifs = $evenement->tarifs()->count();
        if ($nbTarifs >= EvenementController::MAX_TARIFS) {
            return back()->withErrors(['error' => 'Maximum '.EvenementController::MAX_TARIFS.' tarifs par événement.']);
        }

        $jour = $this->jourDemande($request, $evenement);
        $capaciteReference = $jour?->capaciteEffective() ?? (int) $evenement->capacite;

        $validated = $request->validate([
            'nom' => 'required|string|max:100',
            'prix' => 'required|numeric|min:0',
            'evenement_date_id' => [
                'nullable',
                'integer',
                Rule::exists('evenement_dates', 'id')->where('evenement_id', $evenement->id),
            ],
            'quantite_disponible' => ['nullable', 'integer', 'min:1', 'max:'.$capaciteReference],
        ], [
            'nom.required' => 'Le nom du tarif est obligatoire.',
            'nom.max' => 'Le nom ne doit pas dépasser 100 caractères.',
            'prix.required' => 'Le prix est obligatoire.',
            'prix.numeric' => 'Le prix doit être un nombre valide.',
            'prix.min' => 'Le prix ne peut pas être négatif.',
            'evenement_date_id.exists' => 'Le jour sélectionné ne fait pas partie de cet événement.',
            'quantite_disponible.integer' => 'La quantité doit être un nombre entier.',
            'quantite_disponible.min' => 'La quantité doit être d\'au moins 1.',
            'quantite_disponible.max' => "La quantité d'un tarif ne peut pas dépasser la capacité de la jauge ({$capaciteReference} places).",
        ]);

        // Événement gratuit : un billet unique, valable tous les jours.
        if ($evenement->gratuit) {
            $validated['evenement_date_id'] = null;
        }

        $this->verifierUnTarifParJour($evenement, $validated['evenement_date_id'] ?? null);

        $this->validerCapaciteTotale(
            $evenement,
            $validated['quantite_disponible'] ?? null,
            null,
            $validated['evenement_date_id'] ?? null,
        );

        $validated['evenement_id'] = $evenement->id;
        $validated['quantite_vendue'] = 0;
        $validated['statut'] = 'actif';
        Tarif::create($validated);

        return redirect()->route('admin.tarifs.index', $evenement->id)
            ->with('success', 'Tarif ajouté avec succès.');
    }

    // Affiche le formulaire d'édition d'un tarif
    public function edit(Evenement $evenement, Tarif $tarif)
    {
        abort_if($evenement->user_id !== Auth::id(), 403);
        $jours = $evenement->dates()->get();

        return view('tarifs.edit', compact('evenement', 'tarif', 'jours'));
    }

    // Met à jour un tarif existant. Le prix est libre s'il n'y a aucune vente,
    // sinon il faut passer par une demande d'approbation (PaxEvent).
    public function update(Request $request, Evenement $evenement, Tarif $tarif)
    {
        abort_if($evenement->user_id !== Auth::id(), 403);
        abort_if($tarif->evenement_id !== $evenement->id, 404);

        $jour = $this->jourDemande($request, $evenement);
        $capaciteReference = $jour?->capaciteEffective() ?? (int) $evenement->capacite;

        $validated = $request->validate([
            'nom' => 'required|string|max:100',
            'prix' => 'nullable|numeric|min:0',
            'evenement_date_id' => [
                'nullable',
                'integer',
                Rule::exists('evenement_dates', 'id')->where('evenement_id', $evenement->id),
            ],
            'quantite_disponible' => [
                'nullable',
                'integer',
                'min:'.max(1, (int) $tarif->quantite_vendue),
                'max:'.$capaciteReference,
            ],
        ], [
            'nom.required' => 'Le nom du tarif est obligatoire.',
            'nom.max' => 'Le nom ne doit pas dépasser 100 caractères.',
            'prix.numeric' => 'Le prix doit être un nombre valide.',
            'prix.min' => 'Le prix ne peut pas être négatif.',
            'evenement_date_id.exists' => 'Le jour sélectionné ne fait pas partie de cet événement.',
            'quantite_disponible.integer' => 'La quantité doit être un nombre entier.',
            'quantite_disponible.min' => 'La quantité doit être d\'au moins 1.',
            'quantite_disponible.max' => "La quantité d'un tarif ne peut pas dépasser la capacité de la jauge ({$capaciteReference} places).",
        ]);

        // Événement gratuit : un billet unique, valable tous les jours.
        if ($evenement->gratuit) {
            $validated['evenement_date_id'] = null;
        }

        if (array_key_exists('evenement_date_id', $validated)) {
            $this->verifierUnTarifParJour($evenement, $validated['evenement_date_id'], $tarif);
        }

        // Le jour n'est pas envoyé quand le sélecteur est masqué : on garde
        // alors celui du tarif pour le contrôle des stocks.
        $this->validerCapaciteTotale(
            $evenement,
            $validated['quantite_disponible'] ?? null,
            $tarif,
            array_key_exists('evenement_date_id', $validated)
                ? $validated['evenement_date_id']
                : $tarif->evenement_date_id,
        );

        $aVendu = $evenement->tarifs()->sum('quantite_vendue') > 0;

        // Si des billets ont déjà été vendus, le prix ne peut plus être modifié
        // directement : passage par une demande d'approbation.
        if ($aVendu && isset($validated['prix']) && (float) $validated['prix'] != (float) $tarif->prix) {
            return back()->withErrors([
                'prix' => 'Des billets ont déjà été vendus : le prix ne peut pas être modifié directement. Utilisez le bouton « Demander une modification » qui sera validé par PaxEvent.',
            ])->withInput();
        }

        if (!$aVendu && isset($validated['prix'])) {
            $validated['prix'] = $validated['prix'];
        } else {
            $validated['prix'] = $tarif->prix;
        }

        $tarif->update($validated);

        return redirect()->route('admin.tarifs.index', $evenement->id)
            ->with('success', 'Tarif modifié avec succès.');
    }

    // Multijours : un seul tarif par jour (les variantes passent par les packs).
    private function verifierUnTarifParJour(Evenement $evenement, ?int $jourId, ?Tarif $tarifExclu = null): void
    {
        if ($jourId === null || ! $evenement->estMultiJour()) {
            return;
        }

        $existe = $evenement->tarifs()
            ->where('evenement_date_id', $jourId)
            ->when($tarifExclu, fn ($q) => $q->whereKeyNot($tarifExclu->getKey()))
            ->exists();

        if ($existe) {
            throw ValidationException::withMessages([
                'evenement_date_id' => 'Ce jour a déjà son tarif : un seul tarif par jour. Modifiez-le, ou proposez un pack pour une autre formule.',
            ]);
        }
    }

    private function jourDemande(Request $request, Evenement $evenement): ?\App\Models\EvenementDate
    {
        $id = $request->input('evenement_date_id');

        return $id && ! $evenement->gratuit ? $evenement->dates()->find($id) : null;
    }

    private function validerCapaciteTotale(
        Evenement $evenement,
        ?int $quantite,
        ?Tarif $tarifExclu = null,
        ?int $jourId = null
    ): void {
        $tarifs = $evenement->tarifs()
            ->when($tarifExclu, fn ($query) => $query->whereKeyNot($tarifExclu->getKey()))
            ->get(['id', 'evenement_date_id', 'quantite_disponible']);
        $quantite = (int) ($quantite ?? 0);

        // Mono-jour : tous les tarifs partagent la capacité de l'événement.
        if (! $evenement->estMultiJour()) {
            $totalPlacesTarifs = (int) $tarifs->sum('quantite_disponible') + $quantite;

            if ($totalPlacesTarifs > (int) $evenement->capacite) {
                throw ValidationException::withMessages([
                    'quantite_disponible' => "La somme des places des tarifs ({$totalPlacesTarifs} places) ne peut pas dépasser la capacité de l'événement ({$evenement->capacite} places).",
                ]);
            }

            return;
        }

        // Multijours : chaque jour accueille ses billets à la journée ET tous
        // les packs. Un nouveau tarif jour est contrôlé sur son jour, un
        // nouveau pack sur chacun des jours.
        $placesPacks = (int) $tarifs->whereNull('evenement_date_id')->sum('quantite_disponible');
        $jours = $jourId !== null
            ? $evenement->dates()->whereKey($jourId)->get()
            : $evenement->dates()->get();

        foreach ($jours as $jour) {
            $placesJour = (int) $tarifs->where('evenement_date_id', $jour->id)->sum('quantite_disponible');
            $total = $placesJour + $placesPacks + $quantite;
            $capaciteJour = $jour->capaciteEffective();

            if ($total > $capaciteJour) {
                throw ValidationException::withMessages([
                    'quantite_disponible' => "{$jour->libelle()} ({$jour->date_debut->isoFormat('ddd D MMM')}) : les billets du jour ({$placesJour}), les packs ({$placesPacks}) et ce tarif ({$quantite}) font {$total} places, au-delà de la jauge du jour ({$capaciteJour} places).",
                ]);
            }
        }
    }

    // Soumet une demande de modification de prix à PaxEvent (utilisé quand des
    // billets ont déjà été vendus).
    public function demanderModificationPrix(Request $request, Evenement $evenement, Tarif $tarif)
    {
        abort_if($evenement->user_id !== Auth::id(), 403);
        abort_if($tarif->evenement_id !== $evenement->id, 403);

        $aVendu = $evenement->tarifs()->sum('quantite_vendue') > 0;
        abort_if(!$aVendu, 403, 'Aucune vente : modifiez directement le prix.');

        $validated = $request->validate([
            'nouveau_prix' => 'required|numeric|min:0',
        ], [
            'nouveau_prix.required' => 'Le nouveau prix est obligatoire.',
            'nouveau_prix.numeric' => 'Le prix doit être un nombre valide.',
            'nouveau_prix.min' => 'Le prix ne peut pas être négatif.',
        ]);

        // Évite les doublons : une demande en attente pour ce tarif existe déjà.
        $existe = $tarif->demandesModification()
            ->where('statut', 'en_attente')
            ->exists();

        if ($existe) {
            return back()->with('error', 'Une demande de modification est déjà en attente pour ce tarif.');
        }

        $demande = DemandeModificationTarif::create([
            'evenement_id' => $evenement->id,
            'tarif_id' => $tarif->id,
            'user_id' => Auth::id(),
            'ancien_prix' => $tarif->prix,
            'nouveau_prix' => $validated['nouveau_prix'],
            'statut' => 'en_attente',
        ]);

        // Notifie PaxEvent par email (super administrateurs) pour traiter la demande.
        $superAdmins = User::where('role', 'super_admin')->get();
        foreach ($superAdmins as $sa) {
            Mail::to($sa->email)->queue(new TarifDemandeNotification($demande));
        }

        return back()->with('success', 'Votre demande de modification de prix a été envoyée à PaxEvent.');
    }

    // Supprime un tarif
    public function destroy(Evenement $evenement, Tarif $tarif)
    {
        abort_if($evenement->user_id !== Auth::id(), 403); // Vérification de propriétaire
        abort_if($tarif->evenement_id !== $evenement->id, 403); // Vérification d'appartenance
        Tarif::destroy($tarif->id);

        return redirect()->route('admin.tarifs.index', $evenement->id)
            ->with('success', 'Tarif supprimé avec succès.');
    }
}
