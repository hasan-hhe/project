<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

Broadcast::channel('notifications', function ($user) {
    return ['id' => $user->id, 'name' => $user->first_name . ' ' . $user->last_name];
});

