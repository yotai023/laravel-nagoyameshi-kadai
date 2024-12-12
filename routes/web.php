<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RestaurantController;


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
    Route::get('home', [Admin\HomeController::class, 'index'])->name('home');

    Route::get('users',[Admin\UserController::class,'index'])->name('users.index');
    Route::get('users/{id}',[Admin\UserController::class,'show'])->name('users.show');

    Route::get('restaurants', [Admin\RestaurantController::class, 'index'])->name('restaurants.index');
    Route::get('restaurants/create', [Admin\RestaurantController::class, 'create'])->name('restaurants.create');
    Route::get('restaurants/{id}', [Admin\RestaurantController::class, 'show'])->name('restaurants.show');
    Route::get('restaurants/{id}/edit', [Admin\RestaurantController::class, 'edit'])->name('restaurants.edit');
});
