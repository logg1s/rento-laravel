<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'reporter_id',
        'reported_user_id',
        'entity_type',
        'entity_id',
        'reason',
        'status',
        'admin_notes',
    ];

    /**
     * Get the user who reported.
     */
    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    /**
     * Get the user who was reported.
     */
    public function reportedUser()
    {
        return $this->belongsTo(User::class, 'reported_user_id');
    }
}