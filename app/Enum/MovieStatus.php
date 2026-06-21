<?php

namespace App\Enum;

enum MovieStatus: string
{
    case ToWatch  = 'to_watch';
    case Watching  = 'watching';
    case Watched   = 'watched';
}
