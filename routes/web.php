<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/do', [\App\Http\Controllers\TestSdk::class, 'do']);
Route::get('/check', [\App\Http\Controllers\TestSdk::class, 'check']);
Route::get('/balance', [\App\Http\Controllers\TestSdk::class, 'balance']);
