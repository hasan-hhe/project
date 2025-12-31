<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'notification_id',
        'is_seen',
        'is_active',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active' , 1);
    }

    public function notification()
    {
        return $this->belongsTo(Notification::class , 'notification_id');
    }
}
