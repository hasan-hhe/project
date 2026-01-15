<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApartmentController;
use App\Http\Controllers\Admin\ApartmentOwnerController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ApartmentController as AdminApartmentController;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\ModificationRequestController;
use App\Http\Controllers\Admin\PendingApprovalController;
use App\Http\Controllers\Admin\WalletController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\ApartmentPhotoController;
use App\Http\Controllers\Admin\GovernorateController;
use App\Http\Controllers\Admin\CityController;

if (!defined('paginateNumber')) {
    define('paginateNumber', 100);
}

Route::get('/', function () {
    return view('welcome');
});

Route::get('/', function () {
    return redirect()->route('admin.auth.login');
});

Route::prefix('admin')->name('admin.')->group(function () {
    // Auth Routes
    Route::get('/login', [AuthController::class, 'getLogin'])->name('auth.login');
    Route::post('/login', [AuthController::class, 'login'])->name('auth.login.post');
    Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
});

// Admin Routes
Route::middleware(['admin'])->prefix('admin')->name('admin.')->group(function () {

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
    Route::post('/apartments/destroy-check', [AdminApartmentController::class, 'destroyCheck'])->name('apartments.destroy-check');
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

    // Modification Requests Routes
    Route::get('/modification-requests', [ModificationRequestController::class, 'index'])->name('modification-requests.index');
    Route::get('/modification-requests/{modificationRequest}', [ModificationRequestController::class, 'show'])->name('modification-requests.show');
    Route::post('/modification-requests/{modificationRequest}/approve', [ModificationRequestController::class, 'approve'])->name('modification-requests.approve');
    Route::post('/modification-requests/{modificationRequest}/reject', [ModificationRequestController::class, 'reject'])->name('modification-requests.reject');
    Route::delete('/modification-requests/{modificationRequest}', [ModificationRequestController::class, 'destroy'])->name('modification-requests.destroy');

    // Pending Approvals Routes
    Route::get('/pending-approvals', [PendingApprovalController::class, 'index'])->name('pending-approvals.index');
    Route::get('/pending-approvals/{user}', [PendingApprovalController::class, 'show'])->name('pending-approvals.show');
    Route::post('/pending-approvals/{user}/approve', [PendingApprovalController::class, 'approve'])->name('pending-approvals.approve');
    Route::post('/pending-approvals/{user}/reject', [PendingApprovalController::class, 'reject'])->name('pending-approvals.reject');
    Route::post('/pending-approvals/approve-multiple', [PendingApprovalController::class, 'approveMultiple'])->name('pending-approvals.approve-multiple');

    // Wallet Routes
    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
    Route::get('/wallet/{user}', [WalletController::class, 'show'])->name('wallet.show');
    Route::post('/wallet/{user}/recharge', [WalletController::class, 'recharge'])->name('wallet.recharge');

    // Reviews Routes
    Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');
    Route::get('/reviews/{review}', [ReviewController::class, 'show'])->name('reviews.show');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
    Route::post('/reviews/destroy-check', [ReviewController::class, 'destroyCheck'])->name('reviews.destroy-check');

    // Apartment Photos Routes
    Route::get('/apartments/{apartment}/photos', [ApartmentPhotoController::class, 'index'])->name('apartments.photos.index');
    Route::post('/apartments/{apartment}/photos', [ApartmentPhotoController::class, 'store'])->name('apartments.photos.store');
    Route::patch('/apartments/{apartment}/photos/{photo}', [ApartmentPhotoController::class, 'update'])->name('apartments.photos.update');
    Route::post('/apartments/{apartment}/photos/{photo}/set-cover', [ApartmentPhotoController::class, 'setCover'])->name('apartments.photos.set-cover');
    Route::delete('/apartments/{apartment}/photos/{photo}', [ApartmentPhotoController::class, 'destroy'])->name('apartments.photos.destroy');

    // Governorates Routes
    Route::get('/governorates', [GovernorateController::class, 'index'])->name('governorates.index');
    Route::get('/governorates/create', [GovernorateController::class, 'create'])->name('governorates.create');
    Route::post('/governorates', [GovernorateController::class, 'store'])->name('governorates.store');
    Route::get('/governorates/{governorate}', [GovernorateController::class, 'show'])->name('governorates.show');
    Route::get('/governorates/{governorate}/edit', [GovernorateController::class, 'edit'])->name('governorates.edit');
    Route::patch('/governorates/{governorate}', [GovernorateController::class, 'update'])->name('governorates.update');
    Route::delete('/governorates/{governorate}', [GovernorateController::class, 'destroy'])->name('governorates.destroy');
    Route::post('/governorates/destroy-check', [GovernorateController::class, 'destroyCheck'])->name('governorates.destroy-check');

    // Cities Routes
    Route::get('/cities', [CityController::class, 'index'])->name('cities.index');
    Route::get('/cities/create', [CityController::class, 'create'])->name('cities.create');
    Route::post('/cities', [CityController::class, 'store'])->name('cities.store');
    Route::get('/cities/{city}', [CityController::class, 'show'])->name('cities.show');
    Route::get('/cities/{city}/edit', [CityController::class, 'edit'])->name('cities.edit');
    Route::patch('/cities/{city}', [CityController::class, 'update'])->name('cities.update');
    Route::delete('/cities/{city}', [CityController::class, 'destroy'])->name('cities.destroy');
    Route::post('/cities/destroy-check', [CityController::class, 'destroyCheck'])->name('cities.destroy-check');
});
