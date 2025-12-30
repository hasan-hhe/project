<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingChange extends Model
{
    protected $fillable = [
        'new_start_date',
        'new_end_date',
        'status',
        'comment'
    ];

    protected function casts(): array
    {
        return [
            'new_start_date' => 'date',
            'new_end_date' => 'date'
        ];
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
