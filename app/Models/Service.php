<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $service_name
 * @property string $service_description
 * @property int $user_id
 * @property int $category_id
 * @property int $location_id
 * @property string|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Benefit> $benefit
 * @property-read int|null $benefit_count
 * @property-read \App\Models\Category $category
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Comment> $comment
 * @property-read int|null $comment_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Image> $image
 * @property-read int|null $image_count
 * @property-read \App\Models\Location $location
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order> $order
 * @property-read int|null $order_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Price> $price
 * @property-read int|null $price_count
 * @property-read \App\Models\User $user
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $userFavorite
 * @property-read int|null $user_favorite_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ViewedServiceLog> $viewedServiceLog
 * @property-read int|null $viewed_service_log_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Service newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Service newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Service query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Service whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Service whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Service whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Service whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Service whereLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Service whereServiceDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Service whereServiceName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Service whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Service whereUserId($value)
 * @mixin \Eloquent
 */
class Service extends Model
{
    use SoftDeletes;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function comment(): HasMany
    {
        return $this->hasMany(Comment::class)->chaperone();
    }

    public function userFavorite(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorite');
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

    public function order(): HasMany
    {
        return $this->hasMany(Order::class)->chaperone();
    }
}
