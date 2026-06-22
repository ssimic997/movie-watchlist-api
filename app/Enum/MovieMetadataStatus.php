<?php

namespace App\Enum;

enum MovieMetadataStatus: string
{
    case PENDING = 'pending';
    case SUCCESSFUL = 'successful';
    case FAILED = 'failed';

}
