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
Route::get('/apartments/favorites', ApartmentController::class . '@getFavoriteApartments')->name('getFavoriteApar');
Route::get('/apartments/{id}', ApartmentController::class . '@show')->name('getAparById');
// Route::get('/checkuser', [Controller1::class, "checkuser"]);
// Route::get('/getalluser', [Controller1::class, "getalluser"]);
// Route::get('/getallproduct', [Controller1::class, "getallproduct"]);
// Route::post('/update_name', [Controller1::class, "update_name"]);
// Route::delete('/deleteproduct/{id}', [Controller1::class, "deleteproduct"]);
// Route::post('/products', [Controller1::class, "create"]);
// Route::post('/test', [Controller1::class, "test"]);

// Route::post('/signup', [SignupController::class, "signup"]);


// Route::middleware(checktoken::class)->group(function () {
//     Route::post('/checkuser_secured', [Controller2::class, "checkuser"]);
//     Route::post('/delete', [Controller3::class, "deleteuser"]);
// });
// {
//         "name":"burger",
//         "discription":"Full",
//         "price":1000
//     },
//     {
//         "name":"salad",
//         "discription":"katchap",
//         "price":500
//     }
