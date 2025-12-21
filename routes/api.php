<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SignupController;
use App\Http\Middleware\checktoken;
use Illuminate\Support\Facades\Route;


Route::prefix("auth")->group(function () {
    Route::post('/register', [SignupController::class, "register"]);
    Route::post('/login', [SignupController::class, "login"]);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/my-profile', [ProfileController::class, 'getUserInformation']);
    Route::post('/logout', [SignupController::class, 'logout']);
});
