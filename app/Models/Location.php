<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property int $id
 * @property float|null $lng
 * @property float|null $lat
 * @property string $location_name
 * @property string|null $real_location_name
 * @property int|null $province_id
 * @property string|null $address
 * @property string|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order> $order
 * @property-read int|null $order_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Service> $service
 * @property-read int|null $service_count
 * @property-read \App\Models\Province|null $province
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereLat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereLng($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereLocationName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Location extends Model
{
    public $fillable = ['location_name', 'lng', 'lat', 'real_location_name', 'province_id', 'address'];

    public function service(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function order(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }
}
