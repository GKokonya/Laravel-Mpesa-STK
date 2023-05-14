<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\STKRequestController;
use App\Http\Controllers\PaymentController;
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


Route::get('/',[STKRequestController::class, 'stk'])->name('stk');

Route::prefix('stk-requests')->name('stk-requests.')->group(function(){
    Route::post('donate', [STKRequestController::class, 'donate'])->name('donate');
    Route::post('verify', [STKRequestController::class, 'verifyStkCallback'])->name('verify');
    Route::get('processing/{checkoutRequestID}', [STKRequestController::class, 'processing'])->name('processing');
    Route::post('confirm', [STKRequestController::class, 'confirmPayment'])->name('confirm');
    Route::get('success', [STKRequestController::class, 'success'])->name('success');
    Route::get('failure', [STKRequestController::class, 'failure'])->name('failure');
    Route::get('/', [STKRequestController::class, 'index'])->name('index');
    Route::get('/{checkoutRequestID}/edit', [STKRequestController::class, 'edit'])->name('edit');
    Route::put('/{checkoutRequestID}/update', [STKRequestController::class, 'update'])->name('update');
});

Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
