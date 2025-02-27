<?php

namespace App\Enums;

enum StatusEnum: int
{
    case CANCELLED = 0;
    case PENDING = 1;
    case WORKING = 2;
    case SUCCESS = 3;
}
