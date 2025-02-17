<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * 
 *
 * @property int $id
 * @property int $service_id
 * @property string $benefit_name
 * @property string|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Price> $price
 * @property-read int|null $price_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Service> $service
 * @property-read int|null $service_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Benefit newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Benefit newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Benefit query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Benefit whereBenefitName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Benefit whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Benefit whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Benefit whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Benefit whereServiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Benefit whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
