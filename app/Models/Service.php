<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
    public function location() : BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function comment() : HasMany
    {
        return $this->hasMany(Comment::class)->chaperone();
    }

    public function userFavorite() : BelongsToMany
    {
        return $this->belongsToMany(User::class, "favorite");
    }

    public function viewedServiceLog(): HasMany
    {
        return $this->hasMany(ViewedServiceLog::class)->chaperone();
    }

    public function benefit(): HasMany
    {
        return $this->hasMany(Benefit::class)->chaperone();
    }

    public function image(): HasMany
    {
        return $this->hasMany(Image::class)->chaperone();
    }

    public function price(): HasMany
    {
        return $this->hasMany(Price::class)->chaperone();
    }
}
