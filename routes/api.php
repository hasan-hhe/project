<?php

use App\Http\Middleware\checktoken;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SignupController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ApartmentController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OwnerApartmentController;

Route::prefix("auth")->group(function () {
    Route::post('/register', [SignupController::class, "register"]);
    Route::post('/login', [SignupController::class, "login"]);
});

// Public Location APIs (no authentication required)
Route::get('/governorates', [LocationController::class, 'getGovernorates']);
Route::get('/cities', [LocationController::class, 'getCities']);
Route::get('/governorates/{id}/cities', [LocationController::class, 'getCitiesByGovernorate']);
Route::get('/governorates/{id}', [LocationController::class, 'getGovernorate']);
Route::get('/cities/{id}', [LocationController::class, 'getCity']);


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
    Route::get('/avatar-image', [ProfileController::class, 'getAvatar']);
    Route::get('/identity-document-image', [ProfileController::class, 'getIdentityDocument']);
    Route::post('/update-profile-info', [ProfileController::class, 'update']);

    // Apartments APIs
    Route::get('/apartments', [ApartmentController::class, 'index'])->name('getApar');
    Route::get('/apartments/favorites', [ApartmentController::class, 'getFavoriteApartments'])->name('getFavoriteApar');
    Route::get('/apartments/{id}', [ApartmentController::class, 'show'])->name('getAparById');
    Route::post('/apartments/{id}/toggle-favorite', [ApartmentController::class, 'toggleFavorite']);
    Route::get('/apartments/{id}/reviews', [ApartmentController::class, 'getReviews']);
    Route::post('/apartments/{id}/reviews', [ApartmentController::class, 'addReview']);

    // Reservations/Bookings APIs
    Route::get('/reservations/my-reservations', [BookingController::class, 'getMyReservations']);
    Route::get('/reservations/{id}', [BookingController::class, 'show']);
    Route::post('/reservations', [BookingController::class, 'store']);
    Route::post('/reservations/{id}/update', [BookingController::class, 'update']);
    Route::post('/reservations/{id}/cancel', [BookingController::class, 'cancel']);
    Route::post('/reservations/{id}/delete', [BookingController::class, 'destroy']);

    // Conversations APIs
    Route::get('/conversations', [ConversationController::class, 'index']);
    Route::get('/conversations/{id}', [ConversationController::class, 'show']);
    Route::post('/conversations', [ConversationController::class, 'store']);
    Route::get('/conversations/{id}/messages', [ConversationController::class, 'getMessages']);
    Route::post('/conversations/{id}/messages', [ConversationController::class, 'sendMessage']);
    Route::post('/conversations/{id}/mark-read', [ConversationController::class, 'markAsRead']);

    // Notifications APIs
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/mark-seen', [NotificationController::class, 'markAsSeen']);
    Route::get('/notifications/unread/count', [NotificationController::class, 'getUnreadCount']);
    Route::get('/notifications/user/list', [NotificationController::class, 'getUserNotifications']);

    // Owner Apartment Management APIs (only for OWNER account type)
    Route::prefix('owner')->group(function () {
        Route::get('/apartments', [OwnerApartmentController::class, 'index']);
        Route::get('/apartments/locations', [OwnerApartmentController::class, 'getLocations']);
        Route::post('/apartments', [OwnerApartmentController::class, 'store']);
        Route::get('/apartments/{id}', [OwnerApartmentController::class, 'show']);
        Route::post('/apartments/{id}', [OwnerApartmentController::class, 'update']);
        Route::delete('/apartments/{id}', [OwnerApartmentController::class, 'destroy']);
        Route::get('/apartments/{id}/photos', [OwnerApartmentController::class, 'getPhotos']);
        Route::post('/apartments/{id}/photos', [OwnerApartmentController::class, 'uploadPhotos']);
        Route::delete('/apartments/{id}/photos/{photoId}', [OwnerApartmentController::class, 'deletePhoto']);
        Route::post('/apartments/{id}/photos/{photoId}/set-cover', [OwnerApartmentController::class, 'setCoverPhoto']);
        Route::get('/apartments/{id}/bookings', [OwnerApartmentController::class, 'getBookings']);
    });
});
