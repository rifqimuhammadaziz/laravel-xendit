<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index']);
Route::get('/{id}', [HomeController::class, 'detail'])->name('detail'); 
Route::post('/payment', [HomeController::class, 'payment'])->name('payment');
Route::get('/notification/{id}', [HomeController::class, 'notification'])->name('notification');
Route::get('/webhook/', [HomeController::class, 'webhook'])->name('webhook');

Auth::routes();

