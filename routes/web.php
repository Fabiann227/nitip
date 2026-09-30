<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DisputeController;
use App\Http\Controllers\EarningsController;
use App\Http\Controllers\ExploreController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderActionController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderFileController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PublicProfileController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\PaymentController as SettingsPaymentController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\TripController;
use App\Http\Controllers\TripJoinController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/layanan', [HomeController::class, 'services'])->name('services');

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->middleware('throttle:10,1')->name('register.store');

    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:login')->name('login.store');

    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->middleware('throttle:6,1')->name('password.email');
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->middleware('throttle:6,1')->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/verify-email', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::post('/verify-email', [EmailVerificationController::class, 'verify'])->middleware('throttle:10,1')->name('verification.verify');
    Route::post('/verify-email/resend', [EmailVerificationController::class, 'resend'])->middleware('throttle:3,1')->name('verification.send');
    Route::get('/verified', [EmailVerificationController::class, 'verified'])->middleware('verified')->name('verification.verified');
});

/*
|--------------------------------------------------------------------------
| Student area (verified & active accounts)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'active'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/nitip', [ExploreController::class, 'index'])->name('explore');

    // REQUEST stream
    Route::get('/requests/create', [RequestController::class, 'create'])->name('requests.create');
    Route::post('/requests', [RequestController::class, 'store'])->name('requests.store');

    // Orders & lifecycle actions
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/claim', [OrderActionController::class, 'claim'])->name('orders.claim');
    Route::post('/orders/{order}/release', [OrderActionController::class, 'release'])->name('orders.release');
    Route::post('/orders/{order}/start', [OrderActionController::class, 'start'])->name('orders.start');
    Route::post('/orders/{order}/deliver', [OrderActionController::class, 'deliver'])->name('orders.deliver');
    Route::post('/orders/{order}/delivered', [OrderActionController::class, 'delivered'])->name('orders.delivered');
    Route::post('/orders/{order}/complete', [OrderActionController::class, 'complete'])->name('orders.complete');
    Route::post('/orders/{order}/cancel', [OrderActionController::class, 'cancel'])->name('orders.cancel');

    // Direct P2P payment (proof upload + verification)
    Route::post('/orders/{order}/payment', [PaymentController::class, 'store'])->name('orders.payment.store');
    Route::post('/orders/{order}/payment/verify', [PaymentController::class, 'verify'])->name('orders.payment.verify');
    Route::post('/orders/{order}/payment/reject', [PaymentController::class, 'reject'])->name('orders.payment.reject');

    // Reviews & disputes
    Route::post('/orders/{order}/reviews', [ReviewController::class, 'store'])->name('orders.reviews.store');
    Route::post('/orders/{order}/disputes', [DisputeController::class, 'store'])->name('orders.disputes.store');
    Route::get('/disputes/{dispute}', [DisputeController::class, 'show'])->name('disputes.show');

    // Private files
    Route::get('/orders/{order}/files/{kind}', [OrderFileController::class, 'show'])->where('kind', 'document|proof|receipt')->name('orders.files');
    Route::get('/disputes/{dispute}/evidence', [OrderFileController::class, 'evidence'])->name('disputes.evidence');
    Route::get('/u/{user}/qris', [OrderFileController::class, 'qris'])->name('users.qris');

    // OFFER stream (trips)
    Route::get('/trips', [TripController::class, 'index'])->name('trips.index');
    Route::get('/trips/create', [TripController::class, 'create'])->name('trips.create');
    Route::post('/trips', [TripController::class, 'store'])->name('trips.store');
    Route::get('/trips/{trip}', [TripController::class, 'show'])->name('trips.show');
    Route::get('/trips/{trip}/edit', [TripController::class, 'edit'])->name('trips.edit');
    Route::put('/trips/{trip}', [TripController::class, 'update'])->name('trips.update');
    Route::post('/trips/{trip}/close', [TripController::class, 'close'])->name('trips.close');
    Route::post('/trips/{trip}/cancel', [TripController::class, 'cancel'])->name('trips.cancel');
    Route::get('/trips/{trip}/join', [TripJoinController::class, 'create'])->name('trips.join');
    Route::post('/trips/{trip}/join', [TripJoinController::class, 'store'])->name('trips.join.store');

    // Misc
    Route::get('/earnings', EarningsController::class)->name('earnings');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::get('/u/{user}', [PublicProfileController::class, 'show'])->name('users.show');

    // Settings
    Route::get('/settings/profile', [ProfileController::class, 'edit'])->name('settings.profile');
    Route::put('/settings/profile', [ProfileController::class, 'update'])->name('settings.profile.update');
    Route::get('/settings/password', [PasswordController::class, 'edit'])->name('settings.password');
    Route::put('/settings/password', [PasswordController::class, 'update'])->name('settings.password.update');
    Route::get('/settings/payment', [SettingsPaymentController::class, 'edit'])->name('settings.payment');
    Route::put('/settings/payment', [SettingsPaymentController::class, 'update'])->name('settings.payment.update');
    Route::delete('/settings/payment', [SettingsPaymentController::class, 'destroy'])->name('settings.payment.destroy');
});

/*
|--------------------------------------------------------------------------
| Admin
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', Admin\DashboardController::class)->name('dashboard');

    Route::get('/users', [Admin\UserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [Admin\UserController::class, 'show'])->name('users.show');
    Route::post('/users/{user}/suspend', [Admin\UserController::class, 'suspend'])->name('users.suspend');
    Route::post('/users/{user}/unsuspend', [Admin\UserController::class, 'unsuspend'])->name('users.unsuspend');
    Route::post('/users/{user}/verify', [Admin\UserController::class, 'verify'])->name('users.verify');

    Route::get('/orders', [Admin\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [Admin\OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/cancel', [Admin\OrderController::class, 'cancel'])->name('orders.cancel');

    Route::get('/trips', [Admin\TripController::class, 'index'])->name('trips.index');
    Route::post('/trips/{trip}/cancel', [Admin\TripController::class, 'cancel'])->name('trips.cancel');

    Route::get('/disputes', [Admin\DisputeController::class, 'index'])->name('disputes.index');
    Route::get('/disputes/{dispute}', [Admin\DisputeController::class, 'show'])->name('disputes.show');
    Route::post('/disputes/{dispute}/resolve', [Admin\DisputeController::class, 'resolve'])->name('disputes.resolve');

    Route::resource('categories', Admin\ServiceCategoryController::class)->except(['show']);

    Route::get('/audit-logs', [Admin\AuditLogController::class, 'index'])->name('audit-logs.index');
});
