<?php

use App\Http\Controllers\Controller1;
use App\Http\Controllers\Controller2;
use App\Http\Controllers\Controller3;
use App\Http\Controllers\SignupController;
use App\Http\Middleware\checktoken;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApartmentController;
use App\Http\Controllers\Admin\ApartmentOwnerController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ApartmentController as AdminApartmentController;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\NotificationController;

Route::get('/', function () {
    return view('welcome');
});

define('paginateNumber', 10);

Route::get('/', function () {
    return redirect()->route('admin.auth.login');
});

// Admin Routes
Route::prefix('admin')->name('admin.')->group(function () {
    // Auth Routes
    Route::get('/login', [AuthController::class, 'getLogin'])->name('auth.login');
    Route::post('/login', [AuthController::class, 'login'])->name('auth.login.post');
    Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');

    // Dashboard Route
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    // Apartment Owners Routes
    Route::get('/apartment-owners', [ApartmentOwnerController::class, 'index'])->name('apartment-owners.index');
    Route::get('/apartment-owners/{owner}', [ApartmentOwnerController::class, 'show'])->name('apartment-owners.show');
    Route::patch('/apartment-owners/{owner}/status', [ApartmentOwnerController::class, 'updateStatus'])->name('apartment-owners.update-status');

    // Users Routes
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::patch('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::post('/users/destroy-check', [UserController::class, 'destroyCheck'])->name('users.destroy-check');
    Route::get('/users/search', [UserController::class, 'search'])->name('users.search');

    // Apartments Routes
    Route::get('/apartments', [AdminApartmentController::class, 'index'])->name('apartments.index');
    Route::get('/apartments/create', [AdminApartmentController::class, 'create'])->name('apartments.create');
    Route::post('/apartments', [AdminApartmentController::class, 'store'])->name('apartments.store');
    Route::get('/apartments/{apartment}', [AdminApartmentController::class, 'show'])->name('apartments.show');
    Route::get('/apartments/{apartment}/edit', [AdminApartmentController::class, 'edit'])->name('apartments.edit');
    Route::patch('/apartments/{apartment}', [AdminApartmentController::class, 'update'])->name('apartments.update');
    Route::delete('/apartments/{apartment}', [AdminApartmentController::class, 'destroy'])->name('apartments.destroy');
    Route::get('/apartments/{apartment}/toggle-active', [AdminApartmentController::class, 'toggleActive'])->name('apartments.toggle-active');

    // Bookings Routes
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::patch('/bookings/{booking}/status', [BookingController::class, 'updateStatus'])->name('bookings.update-status');
    Route::delete('/bookings/{booking}', [BookingController::class, 'destroy'])->name('bookings.destroy');

    // Notifications Routes
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/create', [NotificationController::class, 'create'])->name('notifications.create');
    Route::post('/notifications', [NotificationController::class, 'store'])->name('notifications.store');
    Route::get('/notifications/{notification}', [NotificationController::class, 'show'])->name('notifications.show');
    Route::get('/notifications/{notification}/edit', [NotificationController::class, 'edit'])->name('notifications.edit');
    Route::patch('/notifications/{notification}', [NotificationController::class, 'update'])->name('notifications.update');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::post('/notifications/mark-all', [NotificationController::class, 'markAll'])->name('notifications.mark-all');
    Route::post('/notifications/{notification}/mark-seen', [NotificationController::class, 'markAsSeen'])->name('notifications.mark-seen');
    Route::get('/notifications/unread/count', [NotificationController::class, 'getUnreadCount'])->name('notifications.unread-count');
    Route::get('/notifications/user/list', [NotificationController::class, 'getUserNotifications'])->name('notifications.user-list');
    Route::get('/notifications/{notification}/toggle-active', [NotificationController::class, 'toggleActive'])->name('notifications.toggle-active');
    Route::post('/notifications/destroy-check', [NotificationController::class, 'destroyCheck'])->name('notifications.destroy-check');
    Route::delete('/notifications/destroy-all', [NotificationController::class, 'destroyAll'])->name('notifications.destroy-all');
});
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
