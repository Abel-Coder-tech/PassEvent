<?php

namespace App\Enums;

enum ScanResult: string
{
    case Valid = 'valide';
    case AlreadyUsed = 'deja_utilise';
    case Invalid = 'invalide';
}
