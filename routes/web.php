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
  Route::get('/clientAccount', [AccountController::class, 'success'])->name('clientAccount');
  Route::post('/transfer-money', [AccountController::class, 'transferMoney'])->name('transfer-money');
  Route::post('/add-currency', [AccountController::class, 'addCurrency'])->name('add-currency');
  Route::post('/buy-crypto', [AccountController::class, 'buyCrypto'])->name('buy-crypto');

