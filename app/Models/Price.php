<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 
 *
 * @property int $id
 * @property string $price_name
 * @property int $price_value
 * @property int $service_id
 * @property string|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Benefit> $benefit
 * @property-read int|null $benefit_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order> $order
 * @property-read int|null $order_count
 * @property-read \App\Models\Service $service
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Price newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Price newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Price query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Price whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Price whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Price whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Price wherePriceName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Price wherePriceValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Price whereServiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Price whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Price extends Model
{
    public $fillable = ['price_name', 'price_value'];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function benefit(): BelongsToMany
    {
        return $this->belongsToMany(Benefit::class);
    }

    public function order(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
