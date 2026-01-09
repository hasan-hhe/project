<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = [
        'renter_id',
        'owner_id',
        'apartment_id'
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

    public function laterMessage()
    {
        $messages = $this->messages;
        $i = null;
        foreach ($messages as $message)
            $i = $message->content;
        return $i;
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
