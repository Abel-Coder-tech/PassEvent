<?php

namespace Tests\Feature;

use App\Mail\OtpEmail;
use App\Models\EmailVerification;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OrganizerPasswordResetTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('nom');
                $table->string('email')->unique();
                $table->string('mot_de_passe');
                $table->string('role')->default('admin');
                $table->string('statut')->default('actif');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('email_verifications')) {
            Schema::create('email_verifications', function (Blueprint $table) {
                $table->id();
                $table->string('email');
                $table->string('code');
                $table->timestamp('expires_at')->nullable();
                $table->timestamp('used_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function test_un_organisateur_reinitialise_son_mot_de_passe_avec_un_code_a_quatre_chiffres(): void
    {
        Mail::fake();
        $user = User::create([
            'nom' => 'Organisateur',
            'email' => 'organisateur-reset@example.com',
            'mot_de_passe' => Hash::make('ancien-secret'),
            'role' => 'admin',
        ]);
        $code = null;

        $this->get(route('login'))->assertOk()
            ->assertSee(route('password.request'));
        $this->post(route('password.email'), ['email' => $user->email])
            ->assertRedirect()
            ->assertSessionHas('success');

        Mail::assertSent(OtpEmail::class, function (OtpEmail $mail) use (&$code, $user) {
            $code = $mail->code;

            return $mail->hasTo($user->email);
        });
        $this->assertMatchesRegularExpression('/^[0-9]{4}$/', $code);

        $this->get(route('password.request'))->assertOk()
            ->assertSee('organizer-reset-code')
            ->assertSee('Renvoyer le code');

        $this->post(route('password.verify'), ['code' => $code])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->post(route('password.update'), [
            'mot_de_passe' => 'nouveau-secret-123',
            'mot_de_passe_confirmation' => 'nouveau-secret-123',
        ])->assertRedirect(route('login'))
            ->assertSessionHas('success');

        $this->assertTrue(Hash::check('nouveau-secret-123', $user->fresh()->mot_de_passe));
        $this->assertNotNull(EmailVerification::where('email', $user->email)->first()->used_at);
    }

    public function test_un_code_organisateur_invalide_ou_expire_ne_permet_pas_la_reinitialisation(): void
    {
        Mail::fake();
        $user = User::create([
            'nom' => 'Organisateur',
            'email' => 'organisateur-expire@example.com',
            'mot_de_passe' => Hash::make('ancien-secret'),
            'role' => 'admin',
        ]);

        $this->post(route('password.email'), ['email' => $user->email]);
        EmailVerification::where('email', $user->email)->update(['expires_at' => now()->subMinute()]);

        $this->post(route('password.verify'), ['code' => '1234'])
            ->assertSessionHasErrors('code');
        $this->post(route('password.update'), [
            'mot_de_passe' => 'nouveau-secret-123',
            'mot_de_passe_confirmation' => 'nouveau-secret-123',
        ])->assertRedirect(route('password.request'));

        $this->assertTrue(Hash::check('ancien-secret', $user->fresh()->mot_de_passe));
    }
}
