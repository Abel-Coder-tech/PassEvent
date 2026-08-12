<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Ticket;
use App\Models\Evenement;

$ev = new Evenement();
$ev->titre = 'FITAB Very Long Event Name To Force Wrapping Test';
$ev->date_event = \Carbon\Carbon::parse('2026-11-01 00:00:00');

$t = new Ticket();
$t->nom_tarif = 'Standard';
$t->montant = 0;
$t->transaction_id = '101213546';
$t->code_unique = 'PAX-0RRKR';
$t->statut_paiement = 'payé';
$t->setRelation('evenement', $ev);

$qr = App\Services\QrCodeService::generateDataUri($t->code_unique, 170);
$logo = Ticket::logoBlancDataUri();

$html = view('tickets.pdf.ticket', compact('t', 'qr', 'logo'));
// The view expects variables named ticket, qrCodeDataUri, logoDataUri
$rendered = view('tickets.pdf.ticket', ['ticket' => $t, 'qrCodeDataUri' => $qr, 'logoDataUri' => $logo])->render();
file_put_contents(__DIR__ . '/../storage/app/debug_ticket.html', $rendered);
echo "Saved debug_ticket.html\n";
