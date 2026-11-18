<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\CodeQrExterne;
use App\Models\LotPhysique;
use App\Models\Log;
use App\Services\LotPhysiquePdfService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * QR codes pour des événements non enregistrés sur la plateforme.
 * Comme pour les lots physiques : le super admin génère d'abord les codes (quantité,
 * nom facultatif), puis importe le visuel et positionne le QR sur la page de template.
 */
class QrExterneController extends Controller
{
    public function index()
    {
        $lots = LotPhysique::where('externe', true)
            ->withCount('codesExternes')
            ->latest()
            ->paginate(20);

        return view('superadmin.qr-externes.index', compact('lots'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'quantite' => 'required|integer|min:1|max:1000',
            'nom_evenement' => 'nullable|string|max:150',
        ], [
            'quantite.required' => 'La quantité est obligatoire.',
            'quantite.min' => 'La quantité doit être d\'au moins 1.',
            'quantite.max' => 'La quantité ne doit pas dépasser 1000.',
        ]);

        $nomEvenement = trim((string) ($validated['nom_evenement'] ?? '')) ?: null;
        $quantite = (int) $validated['quantite'];

        $lot = null;
        $derniereErreur = null;

        // Réessaie en cas de collision de code (1062) ou de verrou (1205/1213)
        for ($essai = 1; $essai <= 3; $essai++) {
            try {
                $lot = DB::transaction(function () use ($nomEvenement, $quantite) {
                    $lot = LotPhysique::create([
                        'user_id' => null,
                        'evenement_id' => null,
                        'tarif_id' => null,
                        'externe' => true,
                        'nom_evenement' => $nomEvenement,
                        'nom' => Str::limit($nomEvenement ?? 'QR externe', 60, '').' — '.now()->format('d-m-Y H\\hi'),
                        'quantite' => $quantite,
                        'statut' => LotPhysique::STATUT_GENERE,
                        'auto_genere' => true,
                        'download_count' => 0,
                        'format' => 's1',
                    ]);

                    $codes = [];
                    while (count($codes) < $quantite) {
                        $codes['PAX-'.strtoupper(Str::random(6))] = true;
                    }
                    $maintenant = now();
                    CodeQrExterne::insert(array_map(fn ($code) => [
                        'lot_physique_id' => $lot->id,
                        'code' => $code,
                        'created_at' => $maintenant,
                        'updated_at' => $maintenant,
                    ], array_keys($codes)));

                    Log::create([
                        'type_operation' => 'lot_qr_externe',
                        'ticket_id' => null,
                        'details' => ['lot_id' => $lot->id, 'quantite' => $quantite, 'nom_evenement' => $nomEvenement],
                        'ip' => request()->ip(),
                    ]);

                    return $lot;
                });
                break;
            } catch (QueryException $e) {
                $derniereErreur = $e;
                if (! in_array((int) ($e->errorInfo[1] ?? -1), [1205, 1213, 1062], true)) {
                    throw $e;
                }
                usleep(150_000 * $essai);
            }
        }

        if (! $lot) {
            throw $derniereErreur;
        }

        return redirect()
            ->route('superadmin.tickets-physiques.template', $lot)
            ->with('success', "{$quantite} QR code(s) générés. Importez maintenant le visuel du ticket et placez le QR code.");
    }

    // PDF des QR codes seuls (grille A4, sans visuel), comme la planche des QR générés en ligne
    public function telechargerQr(LotPhysique $lot)
    {
        abort_unless($lot->externe, 404);

        $codes = $lot->ticketsImprimables();
        if ($codes->isEmpty()) {
            return back()->with('error', 'Aucun QR code dans ce lot.');
        }

        $nom = str_replace(['/', '\\'], '-', $lot->nom_evenement ?? 'lot-'.$lot->id);

        return LotPhysiquePdfService::generer($lot, $codes)->download('QR-codes-'.$nom.'.pdf');
    }

    public function destroy(LotPhysique $lot)
    {
        abort_unless($lot->externe, 404);

        if ($lot->template_path) {
            Storage::disk('public')->delete($lot->template_path);
        }
        $lot->delete();

        return redirect()->route('superadmin.qr-externes')->with('success', 'Lot supprimé.');
    }
}
