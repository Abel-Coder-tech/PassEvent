<?php

require __DIR__.'/vendor/autoload.php';

use Dompdf\Dompdf;

$cases = [
    'A_abs_table' => '
        <style>
            html,body{margin:0;height:100%}
            .content{height:80%;background:#eee}
            table.foot{position:absolute;bottom:0;left:0;right:0;width:100%;background:#542680;color:#fff}
        </style>
        <div class="content">CONTENT</div>
        <table class="foot"><tr><td>BILI A</td></tr></table>',
    'B_abs_div' => '
        <style>
            html,body{margin:0;height:100%}
            .content{height:80%;background:#eee}
            div.foot{position:absolute;bottom:0;left:0;right:0;width:100%;background:#542680;color:#fff;padding:10px}
        </style>
        <div class="content">CONTENT</div>
        <div class="foot">BILI B</div>',
    'C_abs_inline_style' => '
        <div style="height:80%;background:#eee">CONTENT</div>
        <div style="position:absolute;bottom:0;left:0;right:0;width:100%;background:#542680;color:#fff;padding:10px">BILI C</div>',
    'D_abs_no_right' => '
        <style>
            html,body{margin:0;height:100%}
            .content{height:80%;background:#eee}
            div.foot{position:absolute;bottom:0;left:0;width:100%;background:#542680;color:#fff;padding:10px}
        </style>
        <div class="content">CONTENT</div>
        <div class="foot">BILI D</div>',
];

$i = 0;
foreach ($cases as $name => $html) {
    $dompdf = new Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper([0, 0, 283, 396], 'portrait');
    $dompdf->render();
    $file = __DIR__.'\\storage\\app\\foot_'.$name.'.pdf';
    file_put_contents($file, $dompdf->output());
    echo $name.' saved, pages='.$dompdf->getCanvas()->get_page_count().PHP_EOL;
}
