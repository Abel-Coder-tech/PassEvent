<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Contrôles d'accès complémentaires : rôles, contournement de verbe et IDOR.
 * Les identités sont toujours prises dans la session/le token, jamais dans l'URL.
 */
class AccessControlSecurityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->createTables();
    }

    private function utilisateur(string $role, string $email, ?array $permissions = null): User
    {
        $id = DB::table('users')->insertGetId([
            'nom' => ucfirst($role),
            'email' => $email,
            'mot_de_passe' => bcrypt('secret'),
            'role' => $role,
            'statut' => 'actif',
            'permissions' => $permissions === null ? null : json_encode($permissions),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return User::findOrFail($id);
    }

    private function evenement(int $userId, string $slug): int
    {
        return DB::table('evenement')->insertGetId([
            'user_id' => $userId,
            'titre' => 'Événement '.$slug,
            'slug' => $slug,
            'statut' => 'publié',
            'date_event' => now()->addDays(10),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function ticket(int $evenementId, string $code, string $statut = 'payé'): int
    {
        return DB::table('ticket')->insertGetId([
            'evenement_id' => $evenementId,
            'code_unique' => $code,
            'qr_signature' => 'signature-'.$code,
            'email_acheteur' => 'acheteur@example.test',
            'telephone_acheteur' => '0600000000',
            'montant' => 1000,
            'statut_paiement' => $statut,
            'date_achat' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_un_invite_est_redirige_du_tableau_de_bord_vers_la_connexion(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
    }

    public function test_un_organisateur_ne_peut_pas_ouvrir_le_superadmin(): void
    {
        $organisateur = $this->utilisateur('admin', 'orga-acces@example.test');

        $this->actingAs($organisateur)
            ->get('/superadmin')
            ->assertRedirect(route('superadmin.login'));
    }

    public function test_un_organisateur_ne_peut_pas_ouvrir_lespace_equipe(): void
    {
        $organisateur = $this->utilisateur('admin', 'orga-equipe@example.test');

        $this->actingAs($organisateur)
            ->get('/equipe/dashboard')
            ->assertRedirect(route('equipe.login'));
    }

    public function test_un_membre_equipe_sans_la_permission_est_refuse(): void
    {
        $membre = $this->utilisateur('equipe', 'support-acces@example.test', [
            'role' => 'support_client',
            'acces' => ['notifications.consulter'],
        ]);

        $this->actingAs($membre, 'superadmin')
            ->get('/superadmin/remboursements')
            ->assertForbidden();

        $this->actingAs($membre, 'superadmin')
            ->get('/equipe/remboursements')
            ->assertForbidden();
    }

    public function test_un_verbe_detourne_par_method_override_est_rejete(): void
    {
        $organisateur = $this->utilisateur('admin', 'orga-method@example.test');
        $evenementId = $this->evenement($organisateur->id, 'method-override');

        // La billetterie n'existe qu'en POST : un _method=PATCH ne doit pas
        // transformer la requête en une autre route autorisée.
        $this->actingAs($organisateur)
            ->post(route('admin.evenements.billetterie', $evenementId), [
                '_method' => 'PATCH',
                'action' => 'fermer',
            ])
            ->assertStatus(405);
    }

    public function test_un_organisateur_ne_peut_pas_consulter_le_ticket_dun_autre(): void
    {
        $orga = $this->utilisateur('admin', 'orga-ticket@example.test');
        $autre = $this->utilisateur('admin', 'autre-ticket@example.test');
        $ticketId = $this->ticket($this->evenement($autre->id, 'ticket-autre'), 'TICKET-ETRANGER');

        $this->actingAs($orga)
            ->get(route('tickets.show', $ticketId))
            ->assertForbidden();
    }

    public function test_un_organisateur_ne_peut_pas_supprimer_le_code_promo_dun_autre(): void
    {
        $orga = $this->utilisateur('admin', 'orga-promo@example.test');
        $autre = $this->utilisateur('admin', 'autre-promo@example.test');
        $evenementId = $this->evenement($autre->id, 'promo-autre');

        $codeId = DB::table('codes_promos')->insertGetId([
            'evenement_id' => $evenementId,
            'tarif_id' => 1,
            'code' => 'PROMO-ETRANGER',
            'actif' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($orga)
            ->delete(route('admin.codes-promos.destroy', $codeId))
            ->assertForbidden();

        $this->assertNotNull(DB::table('codes_promos')->where('id', $codeId)->first());
    }

    public function test_un_organisateur_ne_peut_pas_annuler_le_remboursement_dun_autre(): void
    {
        $orga = $this->utilisateur('admin', 'orga-remb@example.test');
        $autre = $this->utilisateur('admin', 'autre-remb@example.test');
        $ticketId = $this->ticket(
            $this->evenement($autre->id, 'remb-autre'),
            'REMB-ETRANGER',
            'remboursé',
        );

        $this->actingAs($orga)
            ->post(route('tickets.annuler-remboursement', $ticketId))
            ->assertSessionHasErrors('error');

        $this->assertSame(
            'remboursé',
            DB::table('ticket')->where('id', $ticketId)->value('statut_paiement'),
        );
    }

    public function test_les_codes_de_billet_sont_non_devinables(): void
    {
        $codes = collect(range(1, 20))->map(fn () => \App\Models\Ticket::genererCodeSecurise());

        $this->assertSame(20, $codes->unique()->count());
        foreach ($codes as $code) {
            // PAX- + 6 lettres ou chiffres (plus petit QR)
            $this->assertMatchesRegularExpression('/^PAX-[A-Z0-9]{6}$/', $code);
        }
    }

    public function test_matrice_multi_comptes_une_ressource_etrangere_est_toujours_refusee(): void
    {
        $proprietaire = $this->utilisateur('admin', 'proprio-matrice@example.test');
        $intrus = $this->utilisateur('admin', 'intrus-matrice@example.test');
        $ticketId = $this->ticket($this->evenement($proprietaire->id, 'matrice'), 'MATRICE-TICKET');

        $equipe = $this->utilisateur('equipe', 'equipe-matrice@example.test', [
            'role' => 'support_client', 'acces' => ['notifications.consulter'],
        ]);

        // Les agents (scan et vente) sont couverts par ApiAuthenticationSecurityTest :
        // leurs guards ont un layout dédié, incompatible avec celui de l'espace organisateur.
        // Chaque compte ci-dessous rejoue la même requête sur la ressource d'autrui.
        $scenarios = [
            'invité' => [null, 'web', 302],
            'autre organisateur' => [$intrus, 'web', 403],
            'membre équipe limité' => [$equipe, 'superadmin', 403],
        ];

        foreach ($scenarios as $libelle => [$compte, $guard, $attendu]) {
            $requete = $compte === null
                ? $this
                : $this->actingAs($compte, $guard);

            $requete->get(route('tickets.show', $ticketId))
                ->assertStatus($attendu, "Le compte « {$libelle} » ne doit pas lire ce ticket.");
        }

        // Le propriétaire, lui, peut consulter sa ressource.
        $this->actingAs($proprietaire)->get(route('tickets.show', $ticketId))->assertOk();
    }

    public function test_un_evenement_etranger_est_refuse_pour_tous_les_niveaux(): void
    {
        $proprietaire = $this->utilisateur('admin', 'proprio-event@example.test');
        $intrus = $this->utilisateur('admin', 'intrus-event@example.test');
        $evenementId = $this->evenement($proprietaire->id, 'event-prive');

        $this->get(route('admin.evenements.show', $evenementId))->assertRedirect(route('login'));
        $this->actingAs($intrus)->get(route('admin.evenements.show', $evenementId))->assertForbidden();
        $this->actingAs($proprietaire)->get(route('admin.evenements.show', $evenementId))->assertOk();
    }

    private function createTables(): void
    {
        if (! Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->softDeletes();
                $table->string('nom');
                $table->string('email')->unique();
                $table->string('mot_de_passe');
                $table->string('role')->default('admin');
                $table->string('statut')->default('actif');
                $table->text('permissions')->nullable();
                $table->boolean('must_change_password')->default(false);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('evenement')) {
            Schema::create('evenement', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('titre');
                $table->string('slug')->unique();
                $table->string('statut')->default('publié');
                $table->dateTime('date_event')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('evenement_dates')) {
            Schema::create('evenement_dates', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('evenement_id');
                $table->dateTime('date_debut')->nullable();
                $table->unsignedInteger('ordre')->default(0);
            });
        }

        if (! Schema::hasTable('tarifs')) {
            Schema::create('tarifs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('evenement_id');
                $table->string('nom')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('ticket_id');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('ticket')) {
            Schema::create('ticket', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('evenement_id');
                $table->unsignedBigInteger('tarif_id')->nullable();
                $table->string('code_unique')->unique();
                $table->string('qr_signature')->nullable();
                $table->string('email_acheteur')->nullable();
                $table->string('telephone_acheteur')->nullable();
                $table->decimal('montant', 10, 2)->default(0);
                $table->string('statut_paiement')->default('payé');
                $table->dateTime('date_achat')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('codes_promos')) {
            Schema::create('codes_promos', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('evenement_id');
                $table->unsignedBigInteger('tarif_id');
                $table->string('code')->unique();
                $table->boolean('actif')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('demandes_remboursement')) {
            Schema::create('demandes_remboursement', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('organisateur_id');
                $table->unsignedBigInteger('evenement_id')->nullable();
                $table->string('type');
                $table->decimal('montant_total', 10, 2)->default(0);
                $table->text('motif');
                $table->string('statut')->default('en_attente');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('demande_remboursement_ticket')) {
            Schema::create('demande_remboursement_ticket', function (Blueprint $table) {
                $table->unsignedBigInteger('demande_remboursement_id');
                $table->unsignedBigInteger('ticket_id');
                $table->primary(['demande_remboursement_id', 'ticket_id']);
            });
        }

        if (! Schema::hasTable('messages')) {
            Schema::create('messages', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->boolean('lu')->default(false);
                $table->string('objet')->nullable();
                $table->text('message')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('logs')) {
            Schema::create('logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('ticket_id')->nullable();
                $table->string('type_operation');
                $table->json('details')->nullable();
                $table->string('ip')->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('scan_access_codes')) {
            Schema::create('scan_access_codes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('evenement_id');
                $table->string('code');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('agents')) {
            Schema::create('agents', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('evenement_id')->nullable();
                $table->string('nom');
                $table->string('email')->unique();
                $table->string('password');
                $table->boolean('actif')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('agents_vente')) {
            Schema::create('agents_vente', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('evenement_id')->nullable();
                $table->string('nom');
                $table->string('email')->unique();
                $table->string('password');
                $table->boolean('actif')->default(true);
                $table->timestamps();
            });
        }
    }
}
