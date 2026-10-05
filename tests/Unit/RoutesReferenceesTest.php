<?php

namespace Tests\Unit;

use Tests\TestCase;

// Un appel route('nom_inexistant') leve une RouteNotFoundException, que Laravel
// renvoie en 404 : l'utilisateur voit juste "page introuvable" sans savoir que
// c'est un bug. Ce test echoue des qu'un nom de route utilise n'existe plus.
class RoutesReferenceesTest extends TestCase
{
    /** Dossiers ou les appels route() sont analyses. */
    private const DOSSIERS = ['app', 'resources/views', 'routes', 'database'];

    /**
     * Retire les commentaires pour ne pas signaler un route() cité en commentaire
     * ou dans une URL.
     */
    private function sansCommentaires(string $contenu): string
    {
        $contenu = preg_replace('#\{\{--.*?--\}\}#s', '', $contenu);
        $contenu = preg_replace('#/\*.*?\*/#s', '', $contenu);

        return preg_replace('#(^|[^:])//.*$#m', '$1', $contenu);
    }

    public function test_chaque_route_utilisee_existe(): void
    {
        $declarees = [];
        foreach (app('router')->getRoutes() as $route) {
            if ($route->getName()) {
                $declarees[] = $route->getName();
            }
        }

        $manquantes = [];

        foreach (self::DOSSIERS as $dossier) {
            $base = base_path($dossier);
            if (! is_dir($base)) {
                continue;
            }

            $fichiers = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($base));
            foreach ($fichiers as $fichier) {
                if (! $fichier->isFile() || ! str_ends_with($fichier->getFilename(), '.php')) {
                    continue;
                }

                $contenu = $this->sansCommentaires(file_get_contents($fichier->getPathname()));
                preg_match_all('/\broute\(\s*[\'"]([^\'"]+)[\'"]/', $contenu, $trouves, PREG_OFFSET_CAPTURE);

                foreach ($trouves[0] as $i => $appel) {
                    $nom = $trouves[1][$i][0];

                    if (in_array($nom, $declarees, true)) {
                        continue;
                    }

                    $ligne = substr_count(substr($contenu, 0, $appel[1]), "\n") + 1;
                    $relatif = str_replace(base_path().DIRECTORY_SEPARATOR, '', $fichier->getPathname());
                    $manquantes[] = "route('{$nom}') dans {$relatif}:{$ligne}";
                }
            }
        }

        $this->assertSame(
            [],
            $manquantes,
            "Ces appels pointent vers des routes qui n'existent pas (404 au runtime) :\n".implode("\n", $manquantes)
        );
    }
}
