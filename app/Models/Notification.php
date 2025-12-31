<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'title',
        'body',
        'is_active'
    ];

    public function scopeActive($query){
        return $query->where('is_active' , 1);
    }

    public function users()
    {
        return $this->belongsToMany(User::class , 'user_notifications' , 'user_id' ,'notification_id')->withPivot('id');
    }
}
