<?php

namespace App\Enums;

enum TicketPaymentStatus: string
{
    case Pending = 'en_attente';
    case Paid = 'payé';
    case Failed = 'échoué';
    case Refunded = 'remboursé';
    case Cancelled = 'annulé';
}
