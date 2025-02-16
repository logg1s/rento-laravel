<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Benefit extends Model
{
    public function service(): BelongsToMany
    {
        return $this->belongsToMany(Service::class);
    }
    public function price(): BelongsToMany
    {
        return $this->belongsToMany(Price::class);
    }
}
