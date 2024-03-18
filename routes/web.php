<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\BrandsController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FavoritesController;
use App\Http\Controllers\ShoppingCartController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [StoreController::class, 'index'])->name('products.index');
Route::get('/product/{product}', [StoreController::class, 'show'])->name('products.show');

Route::get('/brands/{brand}', [BrandsController::class, 'show'])->name('brands.show');

Route::get('/cart', [ShoppingCartController::class, 'index'])->name('cart')->middleware('auth', 'verified');
Route::post('/cart/{product}', [ShoppingCartController::class, 'add'])->name('cart.add')->middleware('auth', 'verified');
Route::put('/cart/{product}', [ShoppingCartController::class, 'update'])->name('cart.update')->middleware('auth', 'verified');
Route::delete('/cart/{product}', [ShoppingCartController::class, 'delete'])->name('cart.delete')->middleware('auth', 'verified');
Route::get('/checkout', [OrdersController::class, 'checkout'])->name('checkout')->middleware('auth');

Route::get('/discount/remove', [ShoppingCartController::class, 'removeDiscountCode'])->name('discount.remove')->middleware('auth', 'verified');
Route::post('/discount/set', [ShoppingCartController::class, 'setDiscountCode'])->name('discount.set')->middleware('auth', 'verified');

Route::get('/orders', [OrdersController::class, 'index'])->name('orders.index')->middleware('auth', 'verified');
Route::post('/orders', [OrdersController::class, 'store'])->name('orders.store')->middleware('auth', 'verified', 'verified', 'verified');
Route::get('/orders/{order}', [OrdersController::class, 'show'])->name('orders.show')->middleware('auth', 'verified', 'verified');

Route::get('/favorites', [FavoritesController::class, 'favorites'])->name('favorites')->middleware('auth', 'verified', 'verified');
Route::get('/favorites/{product}', [FavoritesController::class, 'toggleFavorite'])->name('favorites.toggle')->middleware('auth', 'verified', 'verified');

Route::get('/profile', [ProfileController::class, 'index'])->name('profile')->middleware('auth', 'verified', 'verified');
Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit')->middleware('auth', 'verified', 'verified');
Route::put('/profile/edit/email', [ProfileController::class, 'updateEmail'])->name('profile.update-email')->middleware('auth', 'verified');
Route::put('/profile/edit/password', [ProfileController::class, 'updatePassword'])->name('profile.update-password')->middleware('auth', 'verified');

Route::get('/login', [AuthController::class, 'login'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'handleLogin'])->name('login.post')->middleware('guest');
Route::get('/register', [AuthController::class, 'register'])->name('register')->middleware('guest');
Route::post('/register', [AuthController::class, 'handleRegister'])->name('register.post')->middleware('guest');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth', 'verified');

Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');


Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return redirect('/');
})->middleware(['auth', 'signed'])->name('verification.verify');


Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();

    return back()->with('message', 'Verification link sent!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');