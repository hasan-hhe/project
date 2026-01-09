<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'content',
        'is_read',
        'read_at',
        'sender_id',
        'conversation_id',
        'attachment_url',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime'
        ];
    }

    public function sender()
    {
        return $this->belongsTo(User::class);
    }

    public function Conversation()
    {
        return $this->belongsTo(Conversation::class);
    }
}
