<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Ticket;
use App\Models\Evenement;
use App\Services\QrCodeService;
use App\Services\TicketPdfService;

$ev = new Evenement();
$ev->titre = 't';
$ev->date_event = \Carbon\Carbon::now();

$t = new Ticket();
$t->nom_tarif = 't';
$t->montant = 0;
$t->transaction_id = 'tx';
$t->code_unique = 'c';
$t->statut_paiement = 'payé';
$t->setRelation('evenement', $ev);

$qr = QrCodeService::generateDataUri($t->code_unique, 170);
$logo = Ticket::logoBlancDataUri();
$pdf = TicketPdfService::generer($t, $qr, $logo);
$dom = $pdf->getDomPdf();
$canvas = $dom->getCanvas();

echo "page_count=" . $canvas->get_page_count() . PHP_EOL;
echo "width(pt)=" . $canvas->get_width() . " height(pt)=" . $canvas->get_height() . PHP_EOL;

$pages = $canvas->get_page_count();
for ($i = 1; $i <= $pages; $i++) {
    $w = $canvas->get_width($i);
    $h = $canvas->get_height($i);
    echo "page $i size pt: $w x $h\n";
}

file_put_contents(__DIR__ . '/../storage/app/debug_ticket.pdf', $pdf->output());
echo "Saved debug_ticket.pdf\n";
