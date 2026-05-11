<?php

use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CityController;
use App\Http\Controllers\Admin\CountryController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\PaymentLogController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SeatTypeController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\ShowController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VenueController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('admin.dashboard')
        : redirect()->route('login');
});

Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle'])->name('auth.google.redirect');
Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback'])->name('auth.google.callback');

     Route::middleware('auth')->group(function () {
    Route::get('/me', function (Request $request) {
        return response()->json($request->user());
    })->name('me');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:admin,manager'])->prefix('admin')->as('admin.')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    Route::get('tags', [TagController::class, 'index'])->name('tags.index');
    Route::post('tags', [TagController::class, 'store'])->name('tags.store');
    Route::put('tags/{tag}', [TagController::class, 'update'])->name('tags.update');
    Route::delete('tags/{tag}', [TagController::class, 'destroy'])->name('tags.destroy');

    Route::get('countries', [CountryController::class, 'index'])->name('countries.index');
    Route::post('countries', [CountryController::class, 'store'])->name('countries.store');
    Route::put('countries/{country}', [CountryController::class, 'update'])->name('countries.update');
    Route::delete('countries/{country}', [CountryController::class, 'destroy'])->name('countries.destroy');

    Route::get('cities', [CityController::class, 'index'])->name('cities.index');
    Route::post('cities', [CityController::class, 'store'])->name('cities.store');
    Route::put('cities/{city}', [CityController::class, 'update'])->name('cities.update');
    Route::delete('cities/{city}', [CityController::class, 'destroy'])->name('cities.destroy');

    Route::resource('events', EventController::class);
    Route::get('seat-types', [SeatTypeController::class, 'index'])->name('seat-types.index');
    Route::post('seat-types', [SeatTypeController::class, 'store'])->name('seat-types.store');
    Route::put('seat-types/{seatType}', [SeatTypeController::class, 'update'])->name('seat-types.update');
    Route::delete('seat-types/{seatType}', [SeatTypeController::class, 'destroy'])->name('seat-types.destroy');
    Route::get('shows', [ShowController::class, 'index'])->name('shows.index');
    Route::get('shows/{show}', [ShowController::class, 'show'])->name('shows.show');
    Route::post('shows/{show}/regenerate-seats', [ShowController::class, 'regenerateSeats'])->name('shows.regenerate-seats');
    Route::patch('shows/{show}/seat-pricing', [ShowController::class, 'updateSeatPricing'])->name('shows.seat-pricing');
    Route::patch('shows/{show}/seat-status', [ShowController::class, 'updateSeatStatus'])->name('shows.seat-status');
    Route::resource('events.shows', ShowController::class)->except(['index', 'show'])->shallow();
    Route::resource('venues', VenueController::class);
    Route::get('bookings/export/csv', [BookingController::class, 'exportCsv'])->name('bookings.export.csv');
    Route::get('bookings/export/pdf', [BookingController::class, 'exportPdf'])->name('bookings.export.pdf');
    Route::post('bookings/{booking}/confirm', [BookingController::class, 'confirm'])->name('bookings.confirm');
    Route::post('bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
    Route::post('bookings/{booking}/refund', [BookingController::class, 'refund'])->name('bookings.refund');
    Route::get('bookings/{booking}/ticket', [BookingController::class, 'ticket'])->name('bookings.ticket');
    Route::resource('bookings', BookingController::class)->only(['index', 'show']);

    Route::get('coupons', [CouponController::class, 'index'])->name('coupons.index');
    Route::post('coupons', [CouponController::class, 'store'])->name('coupons.store');
    Route::put('coupons/{coupon}', [CouponController::class, 'update'])->name('coupons.update');
    Route::delete('coupons/{coupon}', [CouponController::class, 'destroy'])->name('coupons.destroy');

    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/export/csv', [ReportController::class, 'exportCsv'])->name('reports.export.csv');
    Route::get('reports/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.export.pdf');

    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::post('notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');

    Route::get('payment-logs', [PaymentLogController::class, 'index'])->name('payment-logs.index');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->as('admin.')->group(function () {
    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::patch('users/{user}/toggle-block', [UserController::class, 'toggleBlock'])->name('users.toggle-block');
    Route::patch('users/{user}/role', [UserController::class, 'updateRole'])->name('users.update-role');

    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
});

require __DIR__.'/auth.php';
