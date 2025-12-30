<?php

use App\Http\Controllers\Controller1;
use App\Http\Controllers\Controller2;
use App\Http\Controllers\Controller3;
use App\Http\Controllers\SignupController;
use App\Http\Middleware\checktoken;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApartmentController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/apartments', ApartmentController::class . '@index')->name('getApar');
Route::get('/apartments/{id}', ApartmentController::class . '@show')->name('getAparById');
