<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ChannelNotification extends Model
{
    public $incrementing = false;

    public function user(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
