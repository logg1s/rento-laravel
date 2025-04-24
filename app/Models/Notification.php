<?php

namespace App\Models;

use App\Utils\DirtyLog;
use ExpoSDK\Expo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string $body
 * @property string|null $data
 * @property int $is_read
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereIsRead($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereUserId($value)
 * @mixin \Eloquent
 */
use ExpoSDK\ExpoMessage;

class Notification extends Model
{
    protected $fillable = ['title', 'body', 'user_id', 'data', 'is_read'];
    protected $hidden = ['pivot'];
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function sendToUser(int $userId, $title, $body, $data, bool $isSaveToDB = false, string $channelId = 'default')
    {
        $user = User::findOrFail($userId);
        if (!$user->userSetting->is_notification || !$user->expo_token)
            return;

        $message = ['title' => $title, 'body' => $body, 'channelId' => $channelId];
        if ($data) {
            $message['data'] = $data;
        }

        $response = (new Expo)->send([$message])->to($user->expo_token)->push();

        if ($isSaveToDB) {
            $user->notification()->create(['title' => $title, 'body' => $body, 'data' => json_encode($data)]);
        }


        return $response->getData();
    }
}
