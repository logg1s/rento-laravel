<?php

namespace App\Models;

use ExpoSDK\Expo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
/**
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string $message
 * @property string $type
 * @property int $is_read
 * @property string|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereIsRead($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereUserId($value)
 * @property string $body
 * @property string|null $data
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereData($value)
 * @mixin \Eloquent
 */
use ExpoSDK\ExpoMessage;

class Notification extends Model
{
    protected $fillable = ['title', 'body', 'user_id', 'data'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // public function sendToUser(int $userId, $title, $body, $data)
    // {
    //     $user = User::findOrFail($userId);
    //     $message = ['title' => $title, 'body' => $body, 'data' => $data];
    //     $response = (new Expo)->send($message)->to($user->expo_token)->push();
    //     return $response->getData();
    // }

    public static function sendToUser()
    {
        $response = (new Expo)->send([['title' => 'test from laravel', 'body' => 'day la thong bao thu nghiem']])->to('ExponentPushToken[5_BAr_JDUwFKtij-1jvNSN]')->push();
        return $response->getData();
    }
}
