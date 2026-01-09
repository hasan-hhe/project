<?php

use App\Http\Middleware\checktoken;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SignupController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ApartmentController;
use App\Http\Controllers\ConversationController;

Route::prefix("auth")->group(function () {
    Route::post('/register', [SignupController::class, "register"]);
    Route::post('/login', [SignupController::class, "login"]);
});

// Public Apartments API
Route::get('/apartments', [ApartmentController::class, 'index'])->name('getApar');
Route::get('/apartments/{id}', [ApartmentController::class, 'show'])->name('getAparById');

Route::middleware(['auth:sanctum', 'active'])->group(function () {

    Route::prefix("conversation")->group(function () {
        Route::get('/my-conversations', [ConversationController::class, 'index']);
        Route::post('/messages', [ConversationController::class, 'getMessages']);
        Route::post('/create', [ConversationController::class, 'renterStartConversation']);
        Route::post('/send-message', [ConversationController::class, 'sendMessage']);
        Route::post('/delete', [ConversationController::class, 'deleteConversation']);
        Route::post('/delete-message', [ConversationController::class, 'deleteMessage']);
        Route::post('/update-message', [ConversationController::class, 'updateMessage']);
        Route::post('/message-info', [ConversationController::class, 'messageInfo']);
    });

    Route::get('/my-profile', [ProfileController::class, 'show']);
    Route::post('/logout', [SignupController::class, 'logout']);
    Route::get('/apartments/favorites', [ApartmentController::class, 'getFavoriteApartments'])->name('getFavoriteApar');
    Route::get('/avatar-image', [ProfileController::class, 'getAvatar']);
    Route::get('/identity-document-image', [ProfileController::class, 'getIdentityDocument']);
    Route::post('/update-profile-info', [ProfileController::class, 'update']);
});
