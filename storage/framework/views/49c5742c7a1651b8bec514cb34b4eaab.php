<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Planche de tickets - <?php echo e($lot->nom); ?></title>
    <style>
        @page { size: A4 <?php echo e($layout['orientation']); ?>; margin: 0; }
        html, body { font-family: 'DejaVu Sans', sans-serif; margin: 0; padding: 0; }
        * { margin: 0; padding: 0; box-sizing: border-box; }

        .page {
            position: relative;
            width: <?php echo e($pageLargeur); ?>mm;
            height: <?php echo e($pageHauteur); ?>mm;
            overflow: hidden;
            page-break-after: always;
        }
        .page:last-child { page-break-after: auto; }

        .slot {
            position: absolute;
            background-color: #fff;
            overflow: hidden;
            box-shadow: 0 0 0.5px rgba(0,0,0,.12);
        }
        .ticket-bg {
            position: absolute;
            display: block;
            left: <?php echo e($imgLeft); ?>mm;
            top: <?php echo e($imgTop); ?>mm;
            width: <?php echo e($imgW); ?>mm;
            height: <?php echo e($imgH); ?>mm;
            <?php if($templateUrl): ?>
            background-image: url('<?php echo e($templateUrl); ?>');
            background-repeat: no-repeat;
            background-size: 100% 100%;
            <?php endif; ?>
        }
        .qr-zone {
            position: absolute;
            background: #fff;
            border-radius: 1.5mm;
            overflow: hidden;
        }
        .qr-zone img {
            position: absolute;
            display: block;
        }
        .qr-wrap {
            position: absolute;
        }
        .qr-favicon {
            position: absolute;
            top: 50%;
            left: 50%;
            border-radius: 50%;
            background: #fff;
            padding: 0.3mm;
            box-sizing: border-box;
        }

        .pax-band {
            position: absolute;
            text-align: center;
            overflow: hidden;
            box-sizing: border-box;
        }
        .pax-band .pax-code {
            font-size: <?php echo e($paxFontPt); ?>pt;
            font-weight: 700;
            letter-spacing: 0;
            color: #000;
            white-space: nowrap;
            line-height: <?php echo e($paxLineH); ?>mm;
        }

        .coupe-h, .coupe-v {
            position: absolute;
            border: 0;
            background: transparent;
            border-top: 0.15mm dashed #d9d9d9;
        }
        .coupe-h { border-top-style: dashed; }
        .coupe-v { border-left: 0.15mm dashed #d9d9d9; }

        .marge-sign {
            position: absolute;
            color: #9a9a9a;
            letter-spacing: 0.5px;
            line-height: 1.1;
            white-space: nowrap;
        }
    </style>
</head>
<body>
<?php $__currentLoopData = $pages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="page">
        <?php $__currentLoopData = $page; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slotIdx => $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php $pos = $layout['positions'][$slotIdx % $layout['par_page']]; ?>
            <div class="slot" style="left: <?php echo e($pos['x']); ?>mm; top: <?php echo e($pos['y']); ?>mm; width: <?php echo e($layout['slot_largeur']); ?>mm; height: <?php echo e($layout['slot_hauteur']); ?>mm;">
                <?php if($templateUrl): ?>
                    <div class="ticket-bg"></div>
                <?php endif; ?>
                <div class="qr-zone" style="left: <?php echo e($zoneX); ?>mm; top: <?php echo e($zoneY); ?>mm; width: <?php echo e($zoneW); ?>mm; height: <?php echo e($zoneH); ?>mm;">
                    <div class="qr-wrap" style="left: <?php echo e($padX); ?>mm; top: <?php echo e($padTop); ?>mm; width: <?php echo e($qrSize); ?>mm; height: <?php echo e($qrSize); ?>mm;">
                        <img src="<?php echo e($qrs[$ticket->id]); ?>" alt="QR" style="left: 0; top: 0; width: <?php echo e($qrSize); ?>mm; height: <?php echo e($qrSize); ?>mm;">
                        <?php if($faviconDataUri): ?>
                            <img src="<?php echo e($faviconDataUri); ?>" alt="" class="qr-favicon" style="width: <?php echo e($faviconSize); ?>mm; height: <?php echo e($faviconSize); ?>mm; margin-top: -<?php echo e($faviconSize / 2); ?>mm; margin-left: -<?php echo e($faviconSize / 2); ?>mm;">
                        <?php endif; ?>
                    </div>
                    <div class="pax-band" style="top: <?php echo e($bandTop); ?>mm; left: 0; width: 100%; height: <?php echo e($paxBandH); ?>mm; padding: 0 0 <?php echo e($paxBottom); ?>mm;">
                        <div class="pax-code"><?php echo e($ticket->code_unique); ?></div>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <?php $__currentLoopData = $layout['coupes_h']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $y): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="coupe-h" style="top: <?php echo e($y); ?>mm; left: <?php echo e($layout['bloc_gauche']); ?>mm; width: <?php echo e($layout['bloc_largeur']); ?>mm;"></div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php $__currentLoopData = $layout['coupes_v']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $x): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="coupe-v" style="left: <?php echo e($x); ?>mm; top: <?php echo e($layout['bloc_haut']); ?>mm; height: <?php echo e($layout['bloc_hauteur']); ?>mm;"></div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <?php
            $ev = $lot->evenement;
            $tarif = $lot->tarif;
            $legendParts = array_values(array_filter([
                $ev?->titre,
                $ev?->date_event ? \Carbon\Carbon::parse($ev->date_event)->format('d/m/Y') : null,
                $tarif?->nom,
                $tarif && $tarif->prix !== null ? number_format((float) $tarif->prix, 0, ',', ' ').' FCFA' : null,
            ]));
        ?>
        <span class="marge-sign" style="left: <?php echo e($layout['marge_gauche']); ?>mm; bottom: <?php echo e($signBottom); ?>mm; font-size: <?php echo e($signFontPt); ?>pt;"><?php echo e(implode(' — ', $legendParts)); ?></span>
        <span class="marge-sign" style="right: <?php echo e($layout['marge_gauche']); ?>mm; bottom: <?php echo e($signBottom); ?>mm; font-size: <?php echo e($signFontPt); ?>pt;">© <?php echo e(date('Y')); ?> PaxEvent · Billetterie en ligne 100% Bénin</span>
    </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</body>
</html><?php /**PATH /home/abel/Projects/PassEvent/resources/views/tickets/pdf/template.blade.php ENDPATH**/ ?>