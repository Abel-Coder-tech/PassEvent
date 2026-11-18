<?php

namespace Tests\Feature;

use App\Models\LotPhysique;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class QrExterneTest extends TestCase
{
    use RefreshDatabase;

    // Vrai PNG (GD n'est pas toujours disponible pour UploadedFile::fake()->image)
    private function png(): UploadedFile
    {
        $chemin = tempnam(sys_get_temp_dir(), 'qx').'.png';
        copy(public_path('images/Paxagent-cercle.png'), $chemin);

        return new UploadedFile($chemin, 'ticket.png', 'image/png', null, true);
    }

    private function superadmin(): User
    {
        return User::factory()->create(['role' => 'super_admin', 'statut' => 'actif']);
    }

    public function test_generation_d_un_lot_externe_sans_evenement(): void
    {
        Storage::fake('public');
        $sa = $this->superadmin();

        $this->actingAs($sa, 'superadmin')->get(route('superadmin.qr-externes'))->assertOk();

        $reponse = $this->actingAs($sa, 'superadmin')->post(route('superadmin.qr-externes.store'), [
            'quantite' => 12,
            'nom_evenement' => '',
        ]);

        $lot = LotPhysique::where('externe', true)->firstOrFail();
        $reponse->assertRedirect(route('superadmin.tickets-physiques.template', $lot));

        $this->assertNull($lot->evenement_id);
        $this->assertNull($lot->user_id);
        $this->assertNull($lot->nom_evenement);
        $this->assertSame(12, $lot->codesExternes()->count());
        $this->assertSame(12, $lot->codesExternes()->distinct()->count('code'));
        $this->assertNull($lot->template_path);

        // Page de placement du QR accessible
        $this->actingAs($sa, 'superadmin')->get(route('superadmin.tickets-physiques.template', $lot))
            ->assertOk()->assertSee('Événement hors plateforme');

        // Le lot externe n'apparaît pas dans la liste des tickets physiques
        $this->assertSame(12, $lot->ticketsImprimables()->count());
        $this->assertSame(12, $lot->ticketsImprimables()->pluck('id')->unique()->count());
    }

    public function test_le_nom_d_evenement_est_facultatif_mais_conserve(): void
    {
        Storage::fake('public');

        $this->actingAs($this->superadmin(), 'superadmin')->post(route('superadmin.qr-externes.store'), [
            'quantite' => 3,
            'nom_evenement' => 'Soirée privée',
        ])->assertRedirect();

        $lot = LotPhysique::where('externe', true)->firstOrFail();
        $this->assertSame('Soirée privée', $lot->titreEvenement());
    }

    public function test_quantite_obligatoire(): void
    {
        $this->actingAs($this->superadmin(), 'superadmin')
            ->post(route('superadmin.qr-externes.store'), [])
            ->assertSessionHasErrors(['quantite']);
    }

    public function test_placement_du_qr_puis_planche_pdf(): void
    {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('dompdf a besoin de l\'extension GD pour intégrer le visuel.');
        }

        $sa = $this->superadmin();
        $this->actingAs($sa, 'superadmin')->post(route('superadmin.qr-externes.store'), [
            'quantite' => 3,
        ]);
        $lot = LotPhysique::where('externe', true)->firstOrFail();

        $this->actingAs($sa, 'superadmin')->post(route('superadmin.tickets-physiques.template.save', $lot), [
            'template_image' => $this->png(), 'format' => 's1', 'qr_x' => 10, 'qr_y' => 8, 'qr_size' => 30,
        ])->assertSessionHasNoErrors();

        $lot->refresh();

        try {
            $this->assertTrue($lot->aUnTemplate());

            $qr = $this->actingAs($sa, 'superadmin')->get(route('superadmin.qr-externes.qr-pdf', $lot));
            $qr->assertOk();
            $this->assertStringStartsWith('%PDF', $qr->getContent());

            $apercu = $this->actingAs($sa, 'superadmin')->get(route('superadmin.tickets-physiques.template.preview', $lot));
            $apercu->assertOk();

            $planche = $this->actingAs($sa, 'superadmin')->get(route('superadmin.tickets-physiques.planche', $lot));
            $planche->assertOk();
            $this->assertStringStartsWith('%PDF', $planche->getContent());
        } finally {
            // Le visuel est écrit dans le vrai stockage (lu via storage_path par les services PDF)
            Storage::disk('public')->delete($lot->template_path);
        }
    }
}
