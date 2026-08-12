<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Barryvdh\DomPDF\Facade\Pdf;

$html = '<!doctype html><html><head><style>body{margin:0;padding:0;} .box{width:8cm;height:13cm;background:#542680;} .inner{width:6.83cm;height:11.62cm;background:#fff;margin:0.69cm;}</style></head><body><div class="box"><div class="inner"></div></div></body></html>';
$pdf = Pdf::loadHTML($html);
$pdf->setPaper([0,0,8*28.3465,13*28.3465],'portrait');
$pdf->render();
$dom = $pdf->getDomPdf(); $canvas = $dom->getCanvas();
echo 'simple page_count=' . $canvas->get_page_count() . PHP_EOL;
file_put_contents(__DIR__.'/../storage/app/simple_debug.pdf',$pdf->output());
echo 'saved simple_debug.pdf'.PHP_EOL;
