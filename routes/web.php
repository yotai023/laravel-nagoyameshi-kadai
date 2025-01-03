<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\HomeController as AdminHomeController;
use App\Http\Controllers\Admin\UserController as AdminUserController;

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
});
