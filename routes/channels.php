<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

Broadcast::channel('notifications', function ($user) {
    return ['id' => $user->id, 'name' => $user->first_name . ' ' . $user->last_name];
});

Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    // التحقق من أن المستخدم مشارك في المحادثة
    $conversation = \App\Models\Conversation::find($conversationId);

    if (!$conversation) {
        return false;
    }

    // السماح فقط لصاحب الشقة والمستأجر بالاستماع
    return ($conversation->owner_id == $user->id || $conversation->renter_id == $user->id);
});
