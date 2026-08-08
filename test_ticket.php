<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Evenement;
use App\Models\Ticket;
use App\Services\QrCodeService;
use App\Services\TicketPdfService;

function renderTicket(array $d, string $label): void
{
    $ev = new Evenement();
    $ev->titre = 'Journée des partenaires PASS EVENT 2026';
    $ev->type_evenement = 'conference';
    $ev->date_event = \Carbon\Carbon::parse('2026-09-15 09:00:00');
    $ev->lieu = 'Palais des Congrès, Cotonou';

    $t = new Ticket();
    $t->nom_tarif = $d['tarif'];
    $t->montant = $d['montant'];
    $t->transaction_id = $d['transaction_id'] ?? 'tx_000123456789';
    $t->code_unique = $d['code'];
    $t->code_promo_utilise = $d['promo'] ?? null;
    $t->statut_paiement = $d['statut'];
    $t->setRelation('evenement', $ev);

    $qr = QrCodeService::generateDataUri($t->code_unique, 170);
    $logo = Ticket::logoBlancDataUri();
    $pdf = TicketPdfService::generer($t, $qr, $logo);
    $pdf->save(storage_path('app/ticket_'.$label.'.pdf'));
    echo $label.' => pages='.$pdf->getDomPdf()->getCanvas()->get_page_count().PHP_EOL;
}

renderTicket(['tarif' => 'Standard', 'montant' => 5000, 'code' => 'PASS-A1B2C3', 'statut' => 'payé'], 'paye');
renderTicket(['tarif' => 'Standard', 'montant' => 5000, 'code' => 'PASS-D4E5F6', 'promo' => 'WELCOME10', 'statut' => 'payé'], 'payepromo');
renderTicket(['tarif' => 'Gratuit', 'montant' => 0, 'code' => 'PASS-GRATUIT-XYZ', 'statut' => 'payé'], 'gratuit');

echo 'done'.PHP_EOL;
