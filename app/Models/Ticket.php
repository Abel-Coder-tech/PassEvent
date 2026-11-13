<?php

namespace App\Models;

use App\Observers\TicketObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[ObservedBy(TicketObserver::class)]
class Ticket extends Model
{
    use HasFactory;

    protected $table = 'ticket';

    /**
     * Préfixe des codes provisoires portés par les réservations non payées.
     * Un tel code n'est jamais scannable ni imprimé : il ne devient un vrai
     * code PAX- qu'au moment où le paiement est confirmé.
     */
    public const PREFIXE_RESERVATION = 'TMP-';

    protected $fillable = [
        'evenement_id',
        'tarif_id',
        'evenement_date_id',
        'agent_vente_id',
        'source',
        'code_unique',
        'qr_signature',
        'email_acheteur',
        'telephone_acheteur',
        'whatsapp_acheteur',
        'telephone_paiement',
        'nom_acheteur',
        'nom_tarif',
        'montant',
        'montant_reduction',
        'quantite',
        'statut_paiement',
        'transaction_id',
        'idempotency_key',
        'reservation_expire_le',
        'fedapay_transaction_id',
        'methode_paiement',
        'type_paiement',
        'utilise',
        'date_achat',
        'email_envoye_at',
        'email_tentatives',
        'email_derniere_erreur',
        'code_promo_utilise',
        'agent_vente_id',
        'lot_physique_id',
        'affiliate_event_link_id',
        'annule',
        'annule_at',
        'expire_at',
        'dernier_renvoi_at',
        'renvoi_count',
    ];

    public static function methodePaiementLabel(?string $methode, ?string $type = null): string
    {
        return match ($methode) {
            'cash', 'especes' => 'Espèces',
            'mtn' => 'MTN MoMo',
            'moov' => 'Moov Money',
            'celtiis' => 'Celtiis Cash',
            'orange' => 'Orange Money',
            'togocel' => 'Togocel',
            'wave' => 'Wave',
            'airtel' => 'Airtel Money',
            'free' => 'Free Money',
            'mobile_money', null => $type === 'bancaire' ? 'Carte bancaire' : 'Mobile Money',
            default => ucfirst($methode),
        };
    }

    public function getMethodePaiementLabelAttribute(): string
    {
        return static::methodePaiementLabel($this->methode_paiement, $this->type_paiement);
    }

    // Billet valable pour ce jour : soit il est rattaché à ce jour précis,
    // soit il couvre toutes les dates de l'événement (pack / historique).
    public function estValableLeJour(?EvenementDate $jour): bool
    {
        return $jour === null
            || $this->evenement_date_id === null
            || (int) $this->evenement_date_id === (int) $jour->id;
    }

    // Billets qui occupent réellement une place : payés, ou en attente tant
    // que leur réservation n'est pas expirée. Les annulés, remboursés,
    // échoués et expirés sont exclus. Les billets physiques (lots imprimés)
    // sont aussi exclus : ils n'ont jamais été comptés dans la jauge en ligne.
    public function scopeActifsPourCapacite(Builder $query): Builder
    {
        return $query
            ->whereNull('lot_physique_id')
            ->where(fn (Builder $q) => $q->whereNull('source')->orWhere('source', '!=', 'physique'))
            ->whereNull('expire_at')
            ->where(fn (Builder $q) => $q->whereNull('annule')->orWhere('annule', false))
            ->where(function (Builder $q) {
                $q->where('statut_paiement', 'payé')
                    ->orWhere(function (Builder $q) {
                        $q->where('statut_paiement', 'en_attente')
                            ->where(fn (Builder $q2) => $q2
                                ->whereNull('reservation_expire_le')
                                ->orWhere('reservation_expire_le', '>', now()));
                    });
            });
    }

    public function getLabel(): string
    {
        return $this->nom_tarif ?? 'Standard';
    }

    /**
     * Un billet ne peut être délivré (email, PDF public) que s'il est réellement payé.
     * Les statuts en_attente / échoué / remboursé, les annulations et les
     * réservations expirées ne doivent jamais quitter le système.
     */
    public function estDelivrable(): bool
    {
        return $this->statut_paiement === 'payé'
            && ! $this->annule
            && $this->expire_at === null;
    }

    // Restreint une requête aux billets réellement délivrables.
    public function scopeDelivrables($query)
    {
        return $query->where('statut_paiement', 'payé')
            ->where('annule', false)
            ->whereNull('expire_at');
    }

    protected function casts(): array
    {
        return [
            'utilise' => 'boolean',
            'annule' => 'boolean',
            'date_achat' => 'datetime',
            'email_envoye_at' => 'datetime',
            'email_tentatives' => 'integer',
            'montant'             => 'decimal:2',
            'quantite'            => 'integer',
            'reservation_expire_le' => 'datetime',
            'annule_at' => 'datetime',
            'expire_at' => 'datetime',
            'dernier_renvoi_at' => 'datetime',
            'renvoi_count' => 'integer',
        ];
    }

    public function evenement(): BelongsTo
    {
        return $this->belongsTo(Evenement::class);
    }

    public function tarif(): BelongsTo
    {
        return $this->belongsTo(Tarif::class);
    }

    // Jour auquel ce billet donne accès. `null` = valable toutes les dates
    // (packs et billets historiques).
    public function jour(): BelongsTo
    {
        return $this->belongsTo(EvenementDate::class, 'evenement_date_id');
    }

    public function agentVente(): BelongsTo
    {
        return $this->belongsTo(AgentVente::class, 'agent_vente_id');
    }

    public function lotPhysique(): BelongsTo
    {
        return $this->belongsTo(LotPhysique::class, 'lot_physique_id');
    }

    public function affiliateEventLink(): BelongsTo
    {
        return $this->belongsTo(AffiliateEventLink::class, 'affiliate_event_link_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    // Trace des actions (création, confirmation, annulation, expiration, renvoi)
    public function journalActions(): HasMany
    {
        return $this->hasMany(JournalAction::class);
    }

    // Détermine si le ticket a déjà été scanné avec succès aujourd'hui (1 scan par jour civil)
    public function dejaScanneAujourdhui(): bool
    {
        return \App\Models\Log::where('ticket_id', $this->id)
            ->where('type_operation', 'scan')
            ->where('details->resultat', 'valide')
            ->whereDate('created_at', today())
            ->exists();
    }

    // Détermine si le ticket a déjà été scanné avec succès pendant une session
    // (fenêtre début → fin). Couvre les sessions qui traversent minuit, là où
    // le jour civil changerait entre deux scans.
    public function dejaScanneEntre(\Carbon\CarbonInterface $debut, \Carbon\CarbonInterface $fin): bool
    {
        return \App\Models\Log::where('ticket_id', $this->id)
            ->where('type_operation', 'scan')
            ->where('details->resultat', 'valide')
            ->whereBetween('created_at', [$debut, $fin])
            ->exists();
    }

    // Compte le nombre de jours où le ticket a été scanné avec succès (chaque jour ne compte qu'une fois)
    public function nombreJoursScannes(): int
    {
        return \App\Models\Log::where('ticket_id', $this->id)
            ->where('type_operation', 'scan')
            ->where('details->resultat', 'valide')
            ->distinct()
            ->count(\Illuminate\Support\Facades\DB::raw('DATE(created_at)'));
    }

    public static function genererCodeSecurise(): string
    {
        do {
            $code = 'PAX-' . strtoupper(Str::random(5));
        } while (self::where('code_unique', $code)->exists());
        return $code;
    }

    /**
     * Code provisoire d'une réservation en attente de paiement. Il n'ouvre
     * aucun droit : ni PDF, ni email, ni scan. Le code définitif est attribué
     * par TicketObserver dès que le billet devient réellement payé.
     */
    public static function genererCodeReservation(): string
    {
        do {
            $code = self::PREFIXE_RESERVATION.strtoupper(Str::random(10));
        } while (self::where('code_unique', $code)->exists());

        return $code;
    }

    // Vrai uniquement pour un code émis après confirmation du paiement.
    public function aUnCodeDefinitif(): bool
    {
        $code = (string) $this->code_unique;

        return $code !== ''
            && $code !== 'TMP'
            && ! str_starts_with($code, self::PREFIXE_RESERVATION);
    }

    // Référence à afficher à l'acheteur/à l'agent avant confirmation.
    public function referenceAffichage(): string
    {
        if ($this->aUnCodeDefinitif()) {
            return (string) $this->code_unique;
        }

        return (string) ($this->transaction_id ?: 'En attente de paiement');
    }

    // Normalise la saisie d'un code (avec ou sans préfixe) vers le format PAX-XXXXX
    public static function normaliserCodeSaisi(string $code): string
    {
        $code = strtoupper(trim($code));
        $suffixe = preg_replace('/^PAX-?/', '', $code);
        return 'PAX-' . $suffixe;
    }

    // Retrouve un ticket à partir d'un code saisi (préfixe optionnel)
    public static function trouverParCodeSaisi(string $code): ?self
    {
        return self::where('code_unique', self::normaliserCodeSaisi($code))->first();
    }

    public static function logoBlancDataUri(): string
    {
        $path = public_path('images/logo-ticket.png');
        if (!file_exists($path)) {
            return '';
        }
        // GD absent : on renvoie le logo d'origine plutôt que de faire échouer le PDF
        if (!function_exists('imagecreatefrompng')) {
            return 'data:image/png;base64,' . base64_encode(file_get_contents($path));
        }
        $src = @imagecreatefrompng($path);
        if (!$src) {
            return 'data:image/png;base64,' . base64_encode(file_get_contents($path));
        }
        $w = imagesx($src);
        $h = imagesy($src);
        $dst = imagecreatetruecolor($w, $h);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        $trans = imagecolorallocatealpha($dst, 0, 0, 0, 127);
        imagefill($dst, 0, 0, $trans);
        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                $rgba = imagecolorat($src, $x, $y);
                $alpha = ($rgba >> 24) & 0x7F;
                if ($alpha < 127) {
                    $color = imagecolorallocatealpha($dst, 255, 255, 255, $alpha);
                    imagesetpixel($dst, $x, $y, $color);
                }
            }
        }
        ob_start();
        imagepng($dst);
        $data = ob_get_clean();
        imagedestroy($src);
        imagedestroy($dst);
        return 'data:image/png;base64,' . base64_encode($data);
    }

    public static function logoVioletDataUri(): string
    {
        $path = public_path('images/logo-ticket.png');
        if (!file_exists($path)) {
            return '';
        }
        // GD absent : on renvoie le logo d'origine plutôt que de faire échouer le PDF
        if (!function_exists('imagecreatefrompng')) {
            return 'data:image/png;base64,' . base64_encode(file_get_contents($path));
        }
        $src = @imagecreatefrompng($path);
        if (!$src) {
            return 'data:image/png;base64,' . base64_encode(file_get_contents($path));
        }
        $w = imagesx($src);
        $h = imagesy($src);
        $dst = imagecreatetruecolor($w, $h);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        $trans = imagecolorallocatealpha($dst, 0, 0, 0, 127);
        imagefill($dst, 0, 0, $trans);
        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                $rgba = imagecolorat($src, $x, $y);
                $alpha = ($rgba >> 24) & 0x7F;
                if ($alpha < 127) {
                    $color = imagecolorallocatealpha($dst, 96, 33, 131, $alpha);
                    imagesetpixel($dst, $x, $y, $color);
                }
            }
        }
        ob_start();
        imagepng($dst);
        $data = ob_get_clean();
        imagedestroy($src);
        imagedestroy($dst);
        return 'data:image/png;base64,' . base64_encode($data);
    }

    public function estimerHauteurPdf(): float
    {
        $px = 0;
        $px += 66;  // header (14+14 padding + ~18 titre + ~14 event-name + 2 gap)
        $px += 24;  // body padding (14 top + 10 bottom)
        $px += 26;  // event-meta (~15 text + 6 pad-bottom + 5 margin-bottom)
        $rows = 2;
        if ($this->montant > 0) $rows += 1;
        if ($this->code_promo_utilise) $rows += 1;
        $px += $rows * 22; // info-table rows: padding(5+5) + line-height(~12)
        $px += 10;          // info-table margin-bottom
        if ($this->statut_paiement === 'payé') $px += 71; // code-pass: padding(8+8) + label(8+3) + value(24) + margin-bottom(10) + pad-top(8) collapsed
        if ($this->montant <= 0) $px += 18;
        $px += 8;   // hr margin-bottom only (top margin collapsed)
        $px += 221; // qr-block
        $px += 46;  // note (8 margin-top + 16 padding + ~22 text)
        $px += 56;  // footer (28 padding + 28 content)
        return $px * 0.75 + 70;
    }
}