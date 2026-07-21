<?php

namespace App\Http\Controllers;

use App\Models\CodePromo;
use App\Models\Evenement;
use App\Models\Tarif;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CodePromoController extends Controller
{
    /**
     * Page globale de gestion des codes promo
     */
    public function globalIndex(Request $request)
    {
        $user = Auth::user();
        $evenements = Evenement::where('user_id', $user->id)
            ->orderBy('date_event', 'desc')
            ->get();

        $selectedEvent = $request->input('evenement_id');
        $q = $request->input('q');
        $statut = $request->input('statut');

        $query = CodePromo::with(['evenement', 'tarif'])
            ->whereHas('evenement', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });

        if ($selectedEvent) {
            $query->where('evenement_id', $selectedEvent);
        }

        if ($q) {
            $query->where('code', 'like', '%' . $q . '%');
        }

        if ($statut === 'utilise') {
            $query->where('nb_utilisations', '>', 0);
        } elseif ($statut === 'disponible') {
            $query->where('nb_utilisations', 0)->where('actif', true);
        } elseif ($statut === 'expire') {
            $query->where('date_expiration', '<', now());
        } elseif ($statut === 'inactif') {
            $query->where('actif', false);
        }

        $codesPromos = $query->orderByDesc('created_at')->paginate(20);

        $stats = [
            'total' => (clone $query)->count(),
            'actifs' => (clone $query)->where('actif', true)->where('nb_utilisations', 0)->count(),
            'utilises' => (clone $query)->where('nb_utilisations', '>', 0)->count(),
            'reduction_totale' => Ticket::whereIn('code_promo_utilise', (clone $query)->pluck('code'))
                ->sum('montant_reduction'),
        ];

        $tarifs = $selectedEvent
            ? Tarif::where('evenement_id', $selectedEvent)->where('statut', 'actif')->get()
            : collect();

        return view('admin.codes-promos.index', compact(
            'codesPromos',
            'evenements',
            'selectedEvent',
            'q',
            'statut',
            'stats',
            'tarifs'
        ));
    }

    /**
     * Création globale de codes promo
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            // Validation des champs pour la création de codes promo
            'evenement_id' => 'required|exists:evenements,id',
            'tarif_id' => 'required|exists:tarifs,id',
            'type_reduction' => 'required|in:pourcentage,fixe',
            'valeur_reduction' => 'required|numeric|min:0',
            'prefixe' => 'nullable|string|max:10',
            'max_utilisations' => 'nullable|integer|min:1',
            'date_expiration' => 'nullable|date|after:now',
            'count' => 'nullable|integer|min:1|max:100',
        ]);

        // Vérifie que l'événement existe et qu'il appartient à l'utilisateur connecté avant d'autoriser l'action.
        $evenement = Evenement::findOrFail($validated['evenement_id']);

        if ($evenement->user_id !== Auth::id()) {
            abort(403);
        }

        $count = $request->input('count', 1);
        $prefixe = strtoupper(trim($validated['prefixe'] ?? ''));

        for ($i = 0; $i < $count; $i++) {
            $suffixe = strtoupper(Str::random(6));
            $code = $prefixe ? $prefixe . '-' . $suffixe : $suffixe;

            // Assurez-vous que le code promo est unique
            while (CodePromo::where('code', '=', $code, 'and')->exists()) {
                $suffixe = strtoupper(Str::random(6));
                $code = $prefixe ? $prefixe . '-' . $suffixe : $suffixe;
            }

            CodePromo::create([
                'evenement_id' => $validated['evenement_id'],
                'tarif_id' => $validated['tarif_id'],
                'code' => $code,
                'type_reduction' => $validated['type_reduction'],
                'valeur_reduction' => $validated['valeur_reduction'],
                'max_utilisations' => $validated['max_utilisations'] ?? null,
                'date_expiration' => $validated['date_expiration'] ?? null,
            ]);
        }

        return redirect()->route('admin.codes-promos.index')
            ->with('success', "$count code(s) promo généré(s) avec succès pour '{$evenement->titre}'.");
    }

    /**
     * Suppression d'un code promo
     */
    public function destroy(int $id)
    {
        $codePromo = CodePromo::findOrFail($id);

        // Vérifie que le code promo existe et qu'il appartient à l'utilisateur connecté avant d'autoriser l'action.
        if ($codePromo->evenement->user_id !== Auth::id()) {
            abort(403);
        }

        $codePromo->delete();

        return back()->with('success', 'Code promo supprimé.');
    }

    /**
     * Export CSV des codes promo
     */
    public function export(Request $request)
    {
        $user = Auth::user();
        $selectedEvent = $request->input('evenement_id');

        $query = CodePromo::with(['evenement', 'tarif'])
            ->whereHas('evenement', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });

        if ($selectedEvent) {
            $query->where('evenement_id', $selectedEvent);
        }

        if ($request->has('disponibles')) {
            $query->where('nb_utilisations', 0);
        }

        $codes = $query->get();

        $filename = "codes_promos_" . date('Y-m-d_His') . ".csv";
        $handle = fopen("php://output", 'w');
        
        header('Content-Type: text/csv');
        header("Content-Disposition: attachment; filename=$filename");

        fputcsv($handle, ['Code', 'Événement', 'Tarif', 'Réduction', 'Max utilisations', 'Utilisations', 'Expiration', 'Statut']);

        foreach ($codes as $code) {
            $reduction = $code->type_reduction === 'pourcentage' 
                ? $code->valeur_reduction . '%' 
                : number_format($code->valeur_reduction, 0, ',', ' ') . ' F';

            fputcsv($handle, [
                $code->code,
                $code->evenement->titre,
                $code->tarif?->getLabel() ?? '—',
                $reduction,
                $code->max_utilisations ?? 'Illimité',
                $code->nb_utilisations,
                $code->date_expiration?->format('d/m/Y') ?? 'Pas d\'expiration',
                $code->nb_utilisations > 0 ? 'Utilisé' : ($code->actif ? 'Disponible' : 'Inactif'),
            ]);
        }

        fclose($handle);
        exit;
    }
}
