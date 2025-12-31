<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Apartment extends Model
{
        use HasFactory;

        protected $fillable = [
                'owner_id',
                'governorate_id',
                'city_id',
                'title',
                'description',
                'price',
                'rooms_count',
                'address_line',
                'rating_avg',
                'is_active',
                'is_favorite',
        ];

        public function owner()
        {
                return $this->belongsTo(User::class, 'owner_id');
        }

        public function bookings()
        {
                return $this->hasMany(Booking::class, 'apartment_id');
        }

        public function conservation()
        {
                return $this->hasOne(Conservation::class);
        }

        public function reviews()
        {
                return $this->hasMany(Review::class);
        }

        public function photos()
        {
                return $this->hasMany(Photo::class);
        }
}
