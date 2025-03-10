<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $phone_number
 * @property int|null $image_id
 * @property string $password
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Image> $image
 * @property-read int|null $image_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Notification> $notification
 * @property-read int|null $notification_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Message> $receivedMessage
 * @property-read int|null $received_message_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Role> $role
 * @property-read int|null $role_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Message> $sentMessage
 * @property-read int|null $sent_message_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Service> $service
 * @property-read int|null $service_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Service> $serviceFavorite
 * @property-read int|null $service_favorite_count
 * @property-read \App\Models\UserSetting|null $userSetting
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ViewedServiceLog> $viewedServiceLog
 * @property-read int|null $viewed_service_log_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereImageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutTrashed()
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Comment> $comment
 * @property-read int|null $comment_count
 * @mixin \Eloquent
 */
class User extends Authenticatable implements JWTSubject
{
    use Notifiable, SoftDeletes;

    protected $fillable = ['name', 'phone_number', 'password', 'email', 'address', 'image_id', 'is_oauth'];

    protected $with = [
        'image',
        'role',
    ];
    public function order(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(Image::class);
    }

    public function service(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function serviceFavorite(): BelongsToMany
    {
        return $this->BelongsToMany(Service::class, 'favorite');
    }

    public function sentMessage(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessage(): HasMany
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    public function notification(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function role(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function userSetting(): HasOne
    {
        return $this->hasOne(UserSetting::class);
    }

    public function comment(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function viewedServiceLog(): HasMany
    {
        return $this->hasMany(ViewedServiceLog::class);
    }

    public function cancelOrder(): HasMany
    {
        return $this->hasMany(Order::class, 'cancel_by');
    }

    protected $hidden = [
        'password',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return ['role' => $this->role()->pluck('id')];
    }
}
