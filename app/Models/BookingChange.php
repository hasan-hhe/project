<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingChange extends Model
{
    use HasFactory;

    protected $table = 'booking_change';

    protected $fillable = [
        'requested_by_user_id',
        'booking_id',
        'new_start_date',
        'new_end_date',
        'status',
        'comment',
        'admin_comment',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'new_start_date' => 'date',
            'new_end_date' => 'date',
            'reviewed_at' => 'datetime',
        ];
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
