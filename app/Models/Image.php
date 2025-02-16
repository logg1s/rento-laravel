<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Image extends Model
{
    public function service(): BelongsToMany
    {
        return $this->belongsToMany(Service::class);
    }
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function comment(): BelongsToMany
    {
        return $this->belongsToMany(Comment::class);
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
