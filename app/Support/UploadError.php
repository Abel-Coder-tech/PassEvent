<?php

namespace App\Support;

use Illuminate\Http\Request;

class UploadError
{
    public static function pour(Request $request, string $champ): ?string
    {
        $fichier = $request->file($champ);

        if ($fichier && ! $fichier->isValid()) {
            $code = $fichier->getError();

            if (in_array($code, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) {
                return 'L\'image est trop lourde : le serveur accepte au maximum '.self::tailleMax().'. Réduisez sa taille puis réessayez.';
            }

            return 'Le téléversement de l\'image a échoué (code '.$code.'). Réessayez.';
        }

        $code = $_FILES[$champ]['error'] ?? null;

        if (is_int($code) && ! in_array($code, [UPLOAD_ERR_OK, UPLOAD_ERR_NO_FILE], true)) {
            return 'Le téléversement de l\'image a échoué. Vérifiez sa taille (max '.self::tailleMax().') puis réessayez.';
        }

        return null;
    }

    private static function tailleMax(): string
    {
        $valeur = trim((string) ini_get('upload_max_filesize'));

        if ($valeur === '') {
            return '10 Mo';
        }

        $unites = ['K' => ' Ko', 'M' => ' Mo', 'G' => ' Go'];

        return isset($unites[strtoupper(substr($valeur, -1))])
            ? substr($valeur, 0, -1).$unites[strtoupper(substr($valeur, -1))]
            : $valeur;
    }
}
