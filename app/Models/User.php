<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    public function image(): HasOne
    {
        return $this->hasOne(Image::class)->chaperone();
    }

    public function service(): HasMany
    {
        return $this->hasMany(Service::class)->chaperone();
    }

    public function serviceFavorite(): HasMany
    {
        return $this->hasMany(Service::class)->chaperone();
    }

    public function sentMessage(): HasMany
    {
        return $this->hasMany(Message::class, "sender_id")->chaperone();
    }

    public function receivedMessage(): HasMany
    {
        return $this->hasMany(Message::class, "receiver_id")->chaperone();
    }

    public function notification(): HasMany
    {
        return $this->hasMany(Notification::class)->chaperone();
    }

    public function role(): HasMany
    {
        return $this->hasMany(Role::class)->chaperone();
    }

    public function userSetting(): HasOne
    {
        return $this->hasOne(UserSetting::class)->chaperone();
    }

    public function viewedServiceLog(): HasMany
    {
        return $this->hasMany(ViewedServiceLog::class)->chaperone();
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
