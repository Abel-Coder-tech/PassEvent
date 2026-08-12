<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Barryvdh\DomPDF\Facade\Pdf;

$scenarios = [
    'only_div' => '<!doctype html><html><head><style>html,body{margin:0;padding:0;} body{margin:0;padding:0;} .box{width:8cm;height:13cm;background:red;}</style></head><body><div class="box"></div></body></html>',
    'with_page' => '<!doctype html><html><head><style>@page{margin:0;padding:0;size:8cm 13cm;} html,body{margin:0;padding:0;} .box{width:8cm;height:13cm;background:red;}</style></head><body><div class="box"></div></body></html>',
    'with_body_size' => '<!doctype html><html><head><style>html,body{margin:0;padding:0;width:8cm;height:13cm;} .box{width:8cm;height:13cm;background:red;}</style></head><body><div class="box"></div></body></html>',
    'with_body_size_and_box_padding' => '<!doctype html><html><head><style>html,body{margin:0;padding:0;width:8cm;height:13cm;} .box{width:8cm;height:13cm;background:red;padding:0.5cm;box-sizing:border-box;}</style></head><body><div class="box"></div></body></html>',
];

foreach ($scenarios as $name => $html) {
    $pdf = Pdf::loadHTML($html);
    $pdf->setPaper([0,0,8*28.3465,13*28.3465],'portrait');
    $pdf->render();
    $count = $pdf->getDomPdf()->getCanvas()->get_page_count();
    echo $name . ' => ' . $count . PHP_EOL;
}
