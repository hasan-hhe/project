<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    public function renter()
    {
        return $this->belongsTo(User::class);
    }

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function changes()
    {
        return $this->hasMany(BookingChange::class);
    }
}
