<?php

namespace App\Enums;

enum AffiliateCommissionStatus: string
{
    case Pending = 'en_attente';
    case Available = 'disponible';
    case Paid = 'paye';
    case Cancelled = 'annule';
}
