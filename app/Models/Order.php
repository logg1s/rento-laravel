<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property int $service_id
 * @property int $price_id
 * @property int $price_final_value
 * @property int $status
 * @property string $address
 * @property string $phone_number
 * @property string|null $time_start
 * @property string|null $message
 * @property int|null $cancel_by
 * @property string|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $cancelBy
 * @property-read \App\Models\Location|null $location
 * @property-read \App\Models\Price $price
 * @property-read \App\Models\Service $service
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereCancelBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order wherePhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order wherePriceFinalValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order wherePriceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereServiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereTimeStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereUserId($value)
 * @mixin \Eloquent
 */
class Order extends Model
{
    protected $fillable = [
        'user_id',
        'service_id',
        'price_id',
        'price_final_value',
        'status',
        'address',
        'phone_number',
        'time_start',
        'message',
        'cancel_by',
    ];
    protected $hidden = ['pivot'];
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function price(): BelongsTo
    {
        return $this->belongsTo(Price::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function cancelBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancel_by');
    }
}
