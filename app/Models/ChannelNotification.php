<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * 
 *
 * @property string $id
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $user
 * @property-read int|null $user_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChannelNotification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChannelNotification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChannelNotification query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChannelNotification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChannelNotification whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChannelNotification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChannelNotification whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ChannelNotification extends Model
{
    public $incrementing = false;

    public function user(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
