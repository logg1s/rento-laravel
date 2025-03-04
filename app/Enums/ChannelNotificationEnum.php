<?php

namespace App\Enums;

enum ChannelNotificationEnum: string
{
    case USER = 'user';
    case PROVIDER = 'provider';
    case ADMIN = 'admin';
}
