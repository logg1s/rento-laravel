<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property int $id
 * @property string $email
 * @property string $code
 * @property \Illuminate\Support\Carbon $expires_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailVerification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailVerification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailVerification query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailVerification whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailVerification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailVerification whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailVerification whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailVerification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailVerification whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class EmailVerification extends Model
{
    protected $hidden = ['pivot'];
    protected $fillable = [
        'email',
        'code',
        'expires_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime'
    ];
}