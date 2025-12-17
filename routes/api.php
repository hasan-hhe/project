<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SignupController;
use App\Http\Middleware\checktoken;
use Illuminate\Support\Facades\Route;


// Route::post('/register', [SignupController::class, "register"]); 
Route::name("userinfo")->group(function () {
    Route::post('/register', [SignupController::class, "register"]);
    Route::post('/login', [SignupController::class, "login"]); 
});
// Route::post('/login', [SignupController::class, "login"]); 
Route::post('/logout', [SignupController::class, 'logout'])->middleware('auth:sanctum');
Route::post('/myprofile', [ProfileController::class, 'getuserinformation']);
