<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    use HasFactory;

    protected $table = 'ticket';

    protected $fillable = [
        'evenement_id',
        'tarif_id',
        'agent_vente_id',
        'code_unique',
        'qr_signature',
        'email_acheteur',
        'telephone_acheteur',
        'telephone_paiement',
        'nom_acheteur',
        'nom_tarif',
        'montant',
        'montant_reduction',
        'quantite',
        'statut_paiement',
        'transaction_id',
        'methode_paiement',
        'utilise',
        'date_achat',
        'code_promo_utilise',
        'agent_vente_id',
    ];

    public static function methodePaiementLabel(?string $methode): string
    {
        return match ($methode) {
            'cash', 'especes' => 'Espèces',
            'mtn' => 'MTN MoMo',
            'moov' => 'Moov Money',
            'celtiis' => 'Celtiis Cash',
            'mobile_money', null => 'Mobile',
            default => ucfirst($methode),
        };
    }

    public function getMethodePaiementLabelAttribute(): string
    {
        return static::methodePaiementLabel($this->methode_paiement);
    }

    public function getLabel(): string
    {
        return $this->nom_tarif ?? 'Standard';
    }

    protected function casts(): array
    {
        return [
            'utilise' => 'boolean',
            'date_achat' => 'datetime',
            'montant' => 'decimal:2',
            'quantite' => 'integer',
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

    public function agentVente(): BelongsTo
    {
        return $this->belongsTo(AgentVente::class, 'agent_vente_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public static function logoBlancDataUri(): string
    {
        $path = public_path('images/logo-ticket.png');
        if (!file_exists($path)) {
            return '';
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

    public function estimerHauteurPdf(): float
    {
        $px = 0;
        $px += 66;  // header (14+14 padding + ~18 titre + ~14 event-name + 2 gap)
        $px += 24;  // body padding (14 top + 10 bottom)
        $px += 26;  // event-meta (~15 text + 6 pad-bottom + 5 margin-bottom)
        $rows = 2;
        if ($this->montant > 0) $rows += 1;
        if ($this->montant_reduction > 0) $rows += 1;
        $px += $rows * 22; // info-table rows: padding(5+5) + line-height(~12)
        $px += 10;          // info-table margin-bottom
        if ($this->statut_paiement === 'payé') $px += 71; // code-pass: padding(8+8) + label(8+3) + value(24) + margin-bottom(10) + pad-top(8) collapsed
        if ($this->montant <= 0) $px += 18;
        $px += 8;   // hr margin-bottom only (top margin collapsed)
        $px += 221; // qr-block
        $px += 46;  // note (8 margin-top + 16 padding + ~22 text)
        $px += 56;  // footer (28 padding + 28 content)
        return $px * 0.75 + 40;
    }
}