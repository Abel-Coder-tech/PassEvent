<?php

namespace App\Enums;

enum EventStatus: string
{
    case Draft = 'brouillon';
    case Published = 'publié';
    case Finished = 'terminé';
    case Cancelled = 'annulé';
}
