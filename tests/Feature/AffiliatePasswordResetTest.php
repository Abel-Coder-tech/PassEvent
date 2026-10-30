<?php

namespace Tests\Feature;

use App\Mail\AffiliatePasswordResetOtp;
use App\Models\Affiliate;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AffiliatePasswordResetTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('affiliates')) {
            Schema::create('affiliates', function (Blueprint $table) {
                $table->id();
                $table->string('nom');
                $table->string('prenom')->nullable();
                $table->string('email')->unique();
                $table->string('telephone')->nullable();
                $table->string('password');
                $table->string('code_parrainage')->unique();
                $table->boolean('actif')->default(true);
                $table->string('password_reset_code')->nullable();
                $table->timestamp('password_reset_expires_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function test_un_affilie_peut_reinitialiser_son_mot_de_passe_avec_un_code_email(): void
    {
        Mail::fake();
        $affiliate = $this->createAffiliate();
        $code = null;

        $this->get(route('affiliate.password.request'))->assertOk();
        $this->get(route('affiliate.login'))->assertOk()->assertSee(route('affiliate.password.request'));

        $this->post(route('affiliate.password.email'), ['email' => strtoupper($affiliate->email)])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->get(route('affiliate.password.request'))->assertOk()
            ->assertSee('reset-code')
            ->assertSee('new-password-confirmation');

        Mail::assertSent(AffiliatePasswordResetOtp::class, function (AffiliatePasswordResetOtp $mail) use (&$code, $affiliate) {
            $code = $mail->code;

            return $mail->hasTo($affiliate->email)
                && str_contains($mail->render(), $code);
        });

        $affiliate->refresh();
        $this->assertNotSame($code, $affiliate->password_reset_code);
        $this->assertTrue(Hash::check($code, $affiliate->password_reset_code));
        $this->assertTrue($affiliate->password_reset_expires_at->isFuture());

        $this->post(route('affiliate.password.update'), [
            'code' => $code,
            'password' => 'nouveau-secret-123',
            'password_confirmation' => 'nouveau-secret-123',
        ])->assertRedirect(route('affiliate.login'))
            ->assertSessionHas('success');

        $affiliate->refresh();
        $this->assertTrue(Hash::check('nouveau-secret-123', $affiliate->password));
        $this->assertNull($affiliate->password_reset_code);
        $this->assertNull($affiliate->password_reset_expires_at);

        $this->post(route('affiliate.password.update'), [
            'code' => $code,
            'password' => 'autre-secret-123',
            'password_confirmation' => 'autre-secret-123',
        ])->assertRedirect(route('affiliate.password.request'));
    }

    public function test_la_demande_ne_revele_pas_si_un_compte_affilie_existe(): void
    {
        Mail::fake();

        $this->post(route('affiliate.password.email'), ['email' => 'inconnu@example.com'])
            ->assertRedirect()
            ->assertSessionHas('success');

        Mail::assertNothingSent();
    }

    public function test_un_code_expire_ne_permet_pas_de_changer_le_mot_de_passe(): void
    {
        Mail::fake();
        $affiliate = $this->createAffiliate();
        $code = null;

        $this->post(route('affiliate.password.email'), ['email' => $affiliate->email]);
        Mail::assertSent(AffiliatePasswordResetOtp::class, function (AffiliatePasswordResetOtp $mail) use (&$code) {
            $code = $mail->code;

            return true;
        });

        $affiliate->forceFill(['password_reset_expires_at' => now()->subMinute()])->save();

        $this->post(route('affiliate.password.update'), [
            'code' => $code,
            'password' => 'nouveau-secret-123',
            'password_confirmation' => 'nouveau-secret-123',
        ])->assertSessionHasErrors('code');

        $affiliate->refresh();
        $this->assertTrue(Hash::check('ancien-secret', $affiliate->password));
    }

    private function createAffiliate(): Affiliate
    {
        return Affiliate::create([
            'nom' => 'Test',
            'prenom' => 'Affilié',
            'email' => 'affiliate-reset@example.com',
            'password' => Hash::make('ancien-secret'),
            'code_parrainage' => 'PAXAFF-RESET',
            'actif' => true,
        ]);
    }
}
