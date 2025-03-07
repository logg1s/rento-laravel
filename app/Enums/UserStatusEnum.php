<?php

namespace App\Enums;

enum UserStatusEnum: int
{
    case BLOCKED = 0;
    case PENDING = 1;
    case ACTIVE = 2;
}
