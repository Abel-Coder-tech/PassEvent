<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        html, body { margin: 0; padding: 0; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        .ticket {
            width: <?php echo e($slotW); ?>mm;
            height: <?php echo e($slotH); ?>mm;
            position: relative;
            overflow: hidden;
            background-color: #fff;
        }
        .ticket-bg {
            position: absolute;
            display: block;
        }
        .qr-zone {
            position: absolute;
            left: <?php echo e($zoneX); ?>mm;
            top: <?php echo e($zoneY); ?>mm;
            width: <?php echo e($zoneW); ?>mm;
            height: <?php echo e($zoneH); ?>mm;
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
            left: <?php echo e($padX); ?>mm;
            top: <?php echo e($padTop); ?>mm;
            width: <?php echo e($qrSize); ?>mm;
            height: <?php echo e($qrSize); ?>mm;
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
            top: <?php echo e($bandTop); ?>mm;
            left: 0;
            width: 100%;
            height: <?php echo e($paxBandH); ?>mm;
            padding: 0 0 <?php echo e($paxBottom); ?>mm;
            text-align: center;
            overflow: hidden;
            box-sizing: border-box;
        }
        .pax-band .pax-code {
            font-size: <?php echo e($paxFont); ?>px;
            font-weight: 700;
            letter-spacing: 0;
            color: #000;
            white-space: nowrap;
            line-height: <?php echo e($paxLineH); ?>mm;
        }
    </style>
</head>
<body>
<div class="ticket">
    <?php if($templateUrl): ?>
    <img src="<?php echo e($templateUrl); ?>" alt="" class="ticket-bg" style="left: <?php echo e($imgLeft); ?>mm; top: <?php echo e($imgTop); ?>mm; width: <?php echo e($imgW); ?>mm; height: <?php echo e($imgH); ?>mm;">
<?php endif; ?>
    <div class="qr-zone">
        <div class="qr-wrap">
            <img src="<?php echo e($qrDataUri); ?>" alt="QR" style="left: 0; top: 0; width: <?php echo e($qrSize); ?>mm; height: <?php echo e($qrSize); ?>mm;">
            <?php if(!empty($faviconDataUri)): ?>
                <img src="<?php echo e($faviconDataUri); ?>" alt="" class="qr-favicon" style="width: <?php echo e($faviconSize); ?>mm; height: <?php echo e($faviconSize); ?>mm; margin-top: -<?php echo e($faviconSize / 2); ?>mm; margin-left: -<?php echo e($faviconSize / 2); ?>mm;">
            <?php endif; ?>
        </div>
        <div class="pax-band">
            <div class="pax-code"><?php echo e($codeUnique ?? 'PAX-XXXXX'); ?></div>
        </div>
    </div>
</div>
</body>
</html><?php /**PATH /home/abel/Projects/PassEvent/resources/views/tickets/pdf/ticket-preview.blade.php ENDPATH**/ ?>