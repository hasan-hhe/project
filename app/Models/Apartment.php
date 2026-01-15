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
                'is_recommended',
        ];

        public function owner()
        {
                return $this->belongsTo(User::class, 'owner_id');
        }

        public function bookings()
        {
                return $this->hasMany(Booking::class, 'apartment_id');
        }

        public function Conversation()
        {
                return $this->hasOne(Conversation::class);
        }

        public function reviews()
        {
                return $this->hasMany(Review::class);
        }

        public function photos()
        {
                return $this->hasMany(Photo::class);
        }

        public function favorites()
        {
                return $this->hasMany(Favorite::class);
        }

        public function city()
        {
                return $this->belongsTo(City::class, 'city_id');
        }

        public function governorate()
        {
                return $this->belongsTo(Governorate::class, 'governorate_id');
        }


        public function cover()
        {
                $photos = $this->photos;
                $url = null;
                foreach ($photos as $photo) {
                        if ($photo->is_cover)
                                $url = $photo->url;
                        return $url;
                }
        }
}
