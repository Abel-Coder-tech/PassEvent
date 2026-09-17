<?php

namespace App\Services;

use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class ContratService
{
    /** Résolution de rendu des signatures embarquées (px). L'affichage PDF est de 60 mm de large (voir la vue). */
    public const SIG_WIDTH = 300;
    public const SIG_HEIGHT = 180;

    /** Chemin (relatif à public/) de la signature officielle PaxEvent. */
    public const PAX_SIGNATURE = 'images/Signature/signature-paxevent.jpeg';

    /**
     * Génère le contenu HTML du contrat pour un organisateur.
     */
    public function render(User $user): string
    {
        return view('site.contrat-prestation', $this->viewData($user))->render();
    }

    /**
     * Données passées à la vue du contrat.
     */
    public function viewData(User $user): array
    {
        return [
            'user' => $user,
            'pax_signature_uri' => $this->signatureDataUri(public_path(self::PAX_SIGNATURE)),
            'organisateur_signature_uri' => $user->signature
                ? $this->signatureDataUri(Storage::disk('public')->path($user->signature))
                : null,
            'header_logo_uri' => $this->headerLogoDataUri(),
            'certifie_uri' => $this->certifieDataUri(),
        ];
    }

    /**
     * Logo pour l'en-tête du contrat (data-URI).
     */
    protected function headerLogoDataUri(): ?string
    {
        foreach (['images/logo_paxevent.png', 'images/paxevent_icone1.png', 'favicon.png'] as $rel) {
            $abs = public_path($rel);
            if (is_file($abs)) {
                return 'data:image/png;base64,' . base64_encode((string) file_get_contents($abs));
            }
        }
        return null;
    }

    /**
     * Image "Certifié" pour le coin de chaque page (data-URI).
     */
    protected function certifieDataUri(): ?string
    {
        $abs = public_path('images/Signature/certifie.png');
        if (is_file($abs)) {
            return 'data:image/png;base64,' . base64_encode((string) file_get_contents($abs));
        }
        return null;
    }

    /**
     * Embarque l'image d'une signature en PNG data-URI de SIG_WIDTH x SIG_HEIGHT px.
     * La signature est ajustée au centre d'un fond blanc sans être déformée.
     * Retourne null si l'image est illisible ou introuvable.
     */
    public function signatureDataUri(?string $path): ?string
    {
        if (!$path || !is_file($path) || !function_exists('imagecreatefromstring')) {
            return null;
        }

        $source = @imagecreatefromstring((string) file_get_contents($path));
        if (!$source) {
            return null;
        }

        $sw = imagesx($source);
        $sh = imagesy($source);
        $ratio = min(self::SIG_WIDTH / $sw, self::SIG_HEIGHT / $sh);

        $iw = (int) round($sw * $ratio);
        $ih = (int) round($sh * $ratio);
        $dx = (int) floor((self::SIG_WIDTH - $iw) / 2);
        $dy = (int) floor((self::SIG_HEIGHT - $ih) / 2);

        $canvas = imagecreatetruecolor(self::SIG_WIDTH, self::SIG_HEIGHT);
        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagefill($canvas, 0, 0, $white);
        imagecopyresampled($canvas, $source, $dx, $dy, 0, 0, $iw, $ih, $sw, $sh);

        ob_start();
        imagepng($canvas);
        $png = (string) ob_get_clean();

        return 'data:image/png;base64,' . base64_encode($png);
    }

    /**
     * Génère l'objet PDF du contrat pour un organisateur (téléchargement ou pièce jointe).
     */
    public function pdf(User $user): \Barryvdh\DomPDF\PDF
    {
        $pdf = Pdf::loadHTML($this->render($user));
        $pdf->setPaper('a4', 'portrait');

        return $pdf;
    }

    /**
     * Nom de fichier par défaut du contrat.
     */
    public function filename(User $user): string
    {
        return 'Contrat-Prestation-PaxEvent-' . $user->id . '.pdf';
    }
}