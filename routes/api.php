<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post('/payments', [PaymentController::class, 'create']);
Route::post('/payments/webhook/xendit', [PaymentController::class, 'webhook']);
Route::post('/payments/notification', [PaymentController::class, 'notification']);
