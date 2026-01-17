<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

Broadcast::channel('private-user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

Broadcast::channel('notifications', function ($user) {
    return ['id' => $user->id, 'name' => $user->first_name . ' ' . $user->last_name];
});

Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    $conversation = \App\Models\Conversation::find($conversationId);

    if (!$conversation) {
        return false;
    }

    return ($conversation->owner_id == $user->id || $conversation->renter_id == $user->id);
});
