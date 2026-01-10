<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'conversation_id',
        'sender_id',
        'content',
        'is_read',
        'read_at',
        'sender_id',
        'conversation_id',
        'attachment_url',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime'
        ];
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function Conversation()
    {
        return $this->belongsTo(Conversation::class, 'conversation_id');
    }
}
