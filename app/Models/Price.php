<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Price extends Model
{
    public function service(): BelongsToMany
    {
        return $this->belongsToMany(Service::class);
    }

    public function benefit(): HasMany
    {
        return $this->hasMany(Benefit::class)->chaperone();
    }
}
