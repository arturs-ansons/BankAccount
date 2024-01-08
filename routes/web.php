<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;


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


  Route::get('/', [AuthController::class, 'index'])->name('index');
  Route::post('/login', [AuthController::class, 'login'])->name('login');
  Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
  Route::post('/register', [AuthController::class, 'register'])->name('register');
  Route::get('/dashboard', [AccountController::class, 'success'])->name('dashboard');
  Route::post('/transfer', [AccountController::class, 'transferMoney'])->name('transfer');
  Route::post('/add-currency', [AccountController::class, 'addCurrency'])->name('add-currency');
  Route::post('/buy-crypto', [AccountController::class, 'buyCrypto'])->name('buy-crypto');
  Route::get('/transactions', [AccountController::class, 'transactions'])->name('transactions');
  Route::post('/sell-crypto', [AccountController::class, 'sellCrypto'])->name('sell-crypto');
Route::get('/menu', function () {
    return view('page.menu');
});

Route::get('/transfer', function () {
    return view('page.transfer');
});
Route::get('/add-currency', function () {
    return view('page.add-currency');
});
Route::get('/buy-crypto', function () {
    return view('page.buy-crypto');
});
Route::get('/sell-crypto', function () {
    return view('page.sell-crypto');
});
