<?php

namespace App\Enums;

enum LogOperationType: string
{
    case Purchase = 'achat';
    case Scan = 'scan';
    case Refund = 'remboursement';
    case NotificationSent = 'envoi_notification';
    case PaymentError = 'erreur_paiement';
    case ManualSale = 'vente_manuelle';
}
