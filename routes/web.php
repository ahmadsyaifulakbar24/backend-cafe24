<?php

use App\Http\Controllers\EmailVerificationController;
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

Route::get('/midtrans-snap', function() {
    $snapToken = "e3dfec9e-5e35-4f43-95b8-cc02747a3729";
    return view('midtrans-test.snap.order', compact('snapToken'));
});

// Route Mail Activation
Route::get('email_verification/{token}', [EmailVerificationController::class, 'email_verification'])->name('email_verification');
