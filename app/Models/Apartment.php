<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Apartment extends Model
{
        use HasFactory;

        protected $fillable = [
                'title',
                'description',
                'price',
                'room_count',
                'address_line',
                'is_active',
                'is_favorite'
        ];

        public function owner()
        {
                return $this->belongsTo(User::class);
        }

        public function booking()
        {
                return $this->hasOne(Booking::class);
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
