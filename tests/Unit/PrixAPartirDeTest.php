<?php

namespace Tests\Unit;

use App\Models\Evenement;
use App\Models\Tarif;
use PHPUnit\Framework\TestCase;

class PrixAPartirDeTest extends TestCase
{
    private function evenementAvecPrix(array $prix): Evenement
    {
        $evenement = new Evenement();
        $evenement->setRelation('tarifs', collect($prix)->map(fn ($p) => new Tarif(['prix' => $p])));

        return $evenement;
    }

    public function test_un_jour_gratuit_est_ignore_tant_qu_il_existe_un_tarif_payant(): void
    {
        $this->assertSame(500.0, $this->evenementAvecPrix([0, 1000, 500, 1500])->prixAPartirDe());
    }

    public function test_tous_les_tarifs_gratuits_donnent_zero(): void
    {
        $this->assertSame(0.0, $this->evenementAvecPrix([0, 0])->prixAPartirDe());
    }

    public function test_sans_tarif_pas_de_prix(): void
    {
        $this->assertNull($this->evenementAvecPrix([])->prixAPartirDe());
    }
}
