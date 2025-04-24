<?php
namespace App\Utils;

class DirtyLog
{
    public static function log($data)
    {
        error_log(json_encode($data));
    }
}
