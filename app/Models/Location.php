<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    public function service() : HasMany
    {
        return $this->hasMany(Service::class)->chaperone();
    }
    public function order(): HasMany
    {
        return $this->hasMany(Order::class)->chaperone();
    }
}
