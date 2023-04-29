<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Payments\Mpesa\StkController;
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


Route::get('/',[StkController::class, 'stk'])->name('stk');
Route::post('donate', [StkController::class, 'donate'])->name('donate');
Route::post('process-stk-callback', [StkController::class, 'processStkCallback'])->name('process-stk-callback');
Route::get('processing/{checkoutRequestID}', [StkController::class, 'processing'])->name('processing');
Route::post('confirm-payment', [StkController::class, 'confirmPayment'])->name('confirm-payment');
Route::get('success', [StkController::class, 'success'])->name('success');
Route::get('failure', [StkController::class, 'failture'])->name('failure');

