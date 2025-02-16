<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Category extends Model
{
    public function service() : HasMany
    {
        return $this->hasMany(Service::class)->chaperone();
    }

    public function image(): HasOne
    {
        return $this->hasOne(Image::class)->chaperone();
    }
}
