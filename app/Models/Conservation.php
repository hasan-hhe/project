<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conservation extends Model
{
    protected $fillable = [
        'ably_channel_id'
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function renter()
    {
        return $this->belongsTo(User::class, 'renter_id');
    }

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
