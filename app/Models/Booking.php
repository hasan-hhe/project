<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'renter_id',
        'apartment_id',
        'start_date',
        'end_date',
        'total_price',
        'cancel_reason',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_price' => 'decimal:2',
    ];

    public function renter()
    {
        return $this->belongsTo(User::class, 'renter_id');
    }

    public function apartment()
    {
        return $this->belongsTo(Apartment::class, 'apartment_id');
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
