<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property int $service_id
 * @property string|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Service $service
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ViewedServiceLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ViewedServiceLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ViewedServiceLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ViewedServiceLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ViewedServiceLog whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ViewedServiceLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ViewedServiceLog whereServiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ViewedServiceLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ViewedServiceLog whereUserId($value)
 * @mixin \Eloquent
 */
class ViewedServiceLog extends Model
{
    protected $fillable = [
        'user_id',
        'service_id',
        'updated_at',
    ];
    protected $hidden = ['pivot'];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
