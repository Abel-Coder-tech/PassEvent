<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property string $nom
 * @property string $email
 * @property string $password
 * @property int $evenement_id
 * @property string $code_acces
 * @property bool $actif
 * @property int $tentatives_code
 * @property \Illuminate\Support\Carbon|null $bloque_jusqua
 * @property \Illuminate\Support\Carbon|null $dernier_acces
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Evenement $evenement
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Log> $logs
 * @property-read int|null $logs_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Agent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Agent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Agent query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Agent whereActif($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Agent whereBloqueJusqua($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Agent whereCodeAcces($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Agent whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Agent whereDernierAcces($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Agent whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Agent whereEvenementId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Agent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Agent whereNom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Agent wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Agent whereTentativesCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Agent whereUpdatedAt($value)
 */
	class Agent extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $nom
 * @property string $email
 * @property string $password
 * @property int $evenement_id
 * @property string $code_vente
 * @property bool $actif
 * @property-read int|null $tickets_count
 * @property numeric $montant_total
 * @property \Illuminate\Support\Carbon|null $dernier_acces
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Evenement $evenement
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Ticket> $tickets
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AgentVente newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AgentVente newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AgentVente query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AgentVente whereActif($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AgentVente whereCodeVente($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AgentVente whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AgentVente whereDernierAcces($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AgentVente whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AgentVente whereEvenementId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AgentVente whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AgentVente whereMontantTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AgentVente whereNom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AgentVente wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AgentVente whereTicketsCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AgentVente whereUpdatedAt($value)
 */
	class AgentVente extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $evenement_id
 * @property int $tarif_id
 * @property string $code
 * @property string $type_reduction
 * @property numeric $valeur_reduction
 * @property int|null $max_utilisations
 * @property int $nb_utilisations
 * @property \Illuminate\Support\Carbon|null $date_expiration
 * @property bool $actif
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Evenement $evenement
 * @property-read \App\Models\Tarif $tarif
 * @property-read \App\Models\Ticket|null $ticket
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CodePromo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CodePromo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CodePromo query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CodePromo whereActif($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CodePromo whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CodePromo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CodePromo whereDateExpiration($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CodePromo whereEvenementId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CodePromo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CodePromo whereMaxUtilisations($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CodePromo whereNbUtilisations($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CodePromo whereTarifId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CodePromo whereTypeReduction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CodePromo whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CodePromo whereValeurReduction($value)
 */
	class CodePromo extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $email
 * @property string $code
 * @property \Illuminate\Support\Carbon $expires_at
 * @property \Illuminate\Support\Carbon|null $used_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailVerification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailVerification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailVerification query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailVerification whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailVerification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailVerification whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailVerification whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailVerification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailVerification whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailVerification whereUsedAt($value)
 */
	class EmailVerification extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property string $titre
 * @property string|null $description
 * @property \Illuminate\Support\Carbon $date_event
 * @property string $lieu
 * @property string $categorie
 * @property int $capacite
 * @property int $quota_vendu
 * @property \Illuminate\Support\Carbon|null $date_fin_vente
 * @property string|null $image
 * @property string $statut
 * @property bool $gratuit
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Agent> $agents
 * @property-read int|null $agents_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AgentVente> $agentsVentes
 * @property-read int|null $agents_ventes_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CodePromo> $codesPromos
 * @property-read int|null $codes_promos_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ScanAccessCode> $scanAccessCodes
 * @property-read int|null $scan_access_codes_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Tarif> $tarifs
 * @property-read int|null $tarifs_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Ticket> $tickets
 * @property-read int|null $tickets_count
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Evenement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Evenement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Evenement query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Evenement whereCapacite($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Evenement whereCategorie($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Evenement whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Evenement whereDateEvent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Evenement whereDateFinVente($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Evenement whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Evenement whereGratuit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Evenement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Evenement whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Evenement whereLieu($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Evenement whereQuotaVendu($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Evenement whereStatut($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Evenement whereTitre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Evenement whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Evenement whereUserId($value)
 */
	class Evenement extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $ticket_id
 * @property string $type_operation
 * @property array<array-key, mixed>|null $details
 * @property string|null $ip
 * @property string|null $user_agent
 * @property \Illuminate\Support\Carbon $created_at
 * @property int|null $agent_id
 * @property-read \App\Models\Ticket|null $ticket
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Log newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Log newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Log query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Log whereAgentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Log whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Log whereDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Log whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Log whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Log whereTicketId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Log whereTypeOperation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Log whereUserAgent($value)
 */
	class Log extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $nom_complet
 * @property string $email
 * @property string $objet
 * @property string $message
 * @property bool $lu
 * @property string|null $reponse_admin
 * @property \Illuminate\Support\Carbon|null $date_reponse
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereDateReponse($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereLu($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereNomComplet($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereObjet($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereReponseAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereUpdatedAt($value)
 */
	class Message extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $email
 * @property bool $actif
 * @property \Illuminate\Support\Carbon|null $desabonne_le
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Newsletter actif()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Newsletter newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Newsletter newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Newsletter query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Newsletter whereActif($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Newsletter whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Newsletter whereDesabonneLe($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Newsletter whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Newsletter whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Newsletter whereUpdatedAt($value)
 */
	class Newsletter extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $ticket_id
 * @property string $canal
 * @property string $statut
 * @property string|null $message
 * @property string|null $erreur
 * @property int $tentative
 * @property \Illuminate\Support\Carbon $date_envoi
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Ticket $ticket
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereCanal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereDateEnvoi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereErreur($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereStatut($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereTentative($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereTicketId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereUpdatedAt($value)
 */
	class Notification extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $evenement_id
 * @property string $code
 * @property int $actif
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Evenement $evenement
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanAccessCode newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanAccessCode newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanAccessCode query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanAccessCode whereActif($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanAccessCode whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanAccessCode whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanAccessCode whereEvenementId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanAccessCode whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanAccessCode whereUpdatedAt($value)
 */
	class ScanAccessCode extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $evenement_id
 * @property string $categorie
 * @property string $type
 * @property numeric $prix
 * @property int|null $quantite_disponible
 * @property int $quantite_vendue
 * @property string $statut
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CodePromo> $codesPromos
 * @property-read int|null $codes_promos_count
 * @property-read \App\Models\Evenement $evenement
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Ticket> $tickets
 * @property-read int|null $tickets_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tarif newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tarif newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tarif query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tarif whereCategorie($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tarif whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tarif whereEvenementId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tarif whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tarif wherePrix($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tarif whereQuantiteDisponible($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tarif whereQuantiteVendue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tarif whereStatut($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tarif whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tarif whereUpdatedAt($value)
 */
	class Tarif extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $evenement_id
 * @property int|null $tarif_id
 * @property int|null $agent_vente_id
 * @property string $code_unique
 * @property string $qr_signature
 * @property int $download_count
 * @property string $email_acheteur
 * @property string|null $telephone_acheteur
 * @property string|null $telephone_paiement
 * @property string|null $nom_acheteur
 * @property numeric $montant
 * @property numeric $montant_reduction
 * @property int $quantite
 * @property string $categorie
 * @property string $type
 * @property string $statut_paiement
 * @property string|null $transaction_id
 * @property string|null $methode_paiement
 * @property bool $utilise
 * @property \Illuminate\Support\Carbon $date_achat
 * @property string|null $code_promo_utilise
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\AgentVente|null $agentVente
 * @property-read \App\Models\Evenement $evenement
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Notification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \App\Models\Tarif|null $tarif
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereAgentVenteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereCategorie($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereCodePromoUtilise($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereCodeUnique($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereDateAchat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereDownloadCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereEmailAcheteur($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereEvenementId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereMethodePaiement($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereMontant($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereMontantReduction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereNomAcheteur($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereQrSignature($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereQuantite($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereStatutPaiement($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereTarifId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereTelephoneAcheteur($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereTelephonePaiement($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereUtilise($value)
 */
	class Ticket extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $nom
 * @property string|null $pseudo
 * @property string|null $organisation
 * @property string $email
 * @property string|null $telephone
 * @property string|null $description
 * @property string|null $avatar
 * @property string|null $document_justificatif
 * @property bool $notif_email_evenement
 * @property bool $notif_email_ticket
 * @property bool $notif_email_paiement
 * @property bool $notif_scan
 * @property string|null $fedapay_public_key
 * @property string|null $fedapay_secret_key
 * @property bool $fedapay_active
 * @property string|null $code_acces_scan
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $mot_de_passe
 * @property string $role
 * @property string $statut
 * @property string|null $type universitaire, professionnel
 * @property string|null $type_detail entreprise, association, club
 * @property int $actif
 * @property int $suspendu
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Evenement> $evenements
 * @property-read int|null $evenements_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Withdrawal> $withdrawals
 * @property-read int|null $withdrawals_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereActif($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCodeAccesScan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereDocumentJustificatif($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereFedapayActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereFedapayPublicKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereFedapaySecretKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereMotDePasse($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereNom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereNotifEmailEvenement($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereNotifEmailPaiement($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereNotifEmailTicket($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereNotifScan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereOrganisation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePseudo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereStatut($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereSuspendu($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTelephone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTypeDetail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 */
	class User extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property numeric $montant
 * @property numeric $commission_percentage
 * @property string $nom
 * @property string $mobile
 * @property string $status
 * @property string|null $admin_notes
 * @property \Illuminate\Support\Carbon|null $processed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Withdrawal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Withdrawal newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Withdrawal query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Withdrawal whereAdminNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Withdrawal whereCommissionPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Withdrawal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Withdrawal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Withdrawal whereMobile($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Withdrawal whereMontant($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Withdrawal whereNom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Withdrawal whereProcessedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Withdrawal whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Withdrawal whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Withdrawal whereUserId($value)
 */
	class Withdrawal extends \Eloquent {}
}

