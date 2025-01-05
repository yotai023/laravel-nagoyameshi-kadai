<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\HomeController as AdminHomeController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\RestaurantController as AdminRestaurantController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CompanyController as AdminCompanyController;
use App\Http\Controllers\Admin\TermController as AdminTermController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\TermController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

require __DIR__ . '/auth.php';

Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => 'auth:admin'], function () {
    Route::get('home', [AdminHomeController::class, 'index'])->name('home');

    Route::get('users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('users/{id}', [AdminUserController::class, 'show'])->name('users.show');

    Route::resource('restaurants', AdminRestaurantController::class);

    Route::resource('categories', CategoryController::class);

    Route::resource('company', AdminCompanyController::class)->only(['index', 'edit', 'update']);

    Route::resource('terms', AdminTermController::class)->only(['index', 'edit', 'update']);
});

Route::group(['middleware' => ['guest:admin']], function () {
    // ホームページ
    Route::get('/', [HomeController::class, 'index'])->name('home');

    Route::get('restaurants', [RestaurantController::class, 'index'])->name('restaurants.index');
    Route::get('restaurants/{restaurant}', [RestaurantController::class, 'show'])->name('restaurants.show');

    Route::get('company', [CompanyController::class, 'index'])->name('company.index');
    Route::get('terms', [TermController::class, 'index'])->name('terms.index');

    Route::group(['middleware' => ['auth']], function () {
        // 普通会員
        Route::resource('user', UserController::class)->only(['index', 'edit', 'update']);
        Route::resource('restaurants.reviews', ReviewController::class)->except(['show']);

        // 予約機能
        Route::get('reservations', [ReservationController::class, 'index'])->name('reservations.index');
        Route::get('restaurants/{restaurant}/reservations/create', [ReservationController::class, 'create'])
            ->name('restaurants.reservations.create');
        Route::post('restaurants/{restaurant}/reservations', [ReservationController::class, 'store'])
            ->name('restaurants.reservations.store');
        Route::delete('reservations/{reservation}', [ReservationController::class, 'destroy'])
            ->name('reservations.destroy');

        // 有料プラン登録
        Route::get('subscription/create', [SubscriptionController::class, 'create'])
            ->name('subscription.create');

        Route::post('subscription', [SubscriptionController::class, 'store'])->name('subscription.store');

        // 有料プラン
        Route::group(['middleware' => 'subscribed'], function () {
            // プレミアム機能管理
            Route::get('subscription/edit', [SubscriptionController::class, 'edit'])->name('subscription.edit');
            Route::patch('subscription', [SubscriptionController::class, 'update'])->name('subscription.update');
            Route::get('subscription/cancel', [SubscriptionController::class, 'cancel'])->name('subscription.cancel');
            Route::post('subscription/destroy', [SubscriptionController::class, 'destroy'])
                ->name('subscription.destroy.post');
            Route::delete('subscription', [SubscriptionController::class, 'destroy'])->name('subscription.destroy');

            // お気に入り機能
            Route::get('favorites', [FavoriteController::class, 'index'])->name('favorites.index');
            Route::post('favorites/{restaurant_id}', [FavoriteController::class, 'store'])->name('favorites.store');
            Route::delete('favorites/{restaurant_id}', [FavoriteController::class, 'destroy'])->name('favorites.destroy');
        });
    });
});
