<?php

use App\Http\Middleware\checktoken;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SignupController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ApartmentController;


Route::prefix("auth")->group(function () {
    Route::post('/register', [SignupController::class, "register"]);
    Route::post('/login', [SignupController::class, "login"]);
});

Route::middleware(['auth:sanctum', 'active'])->group(function () {
    Route::get('/my-profile', [ProfileController::class, 'show']);
    Route::post('/logout', [SignupController::class, 'logout']);
    Route::get('/apartments/favorites', [ApartmentController::class, 'getFavoriteApartments'])->name('getFavoriteApar');
    Route::get('/avatar-image', [ProfileController::class, 'getAvatar']);
    Route::get('/identity-document-image', [ProfileController::class, 'getIdentityDocument']);
    Route::put('/update-profile-info', [ProfileController::class, 'update']);
});
