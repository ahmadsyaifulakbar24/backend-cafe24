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

// Route::get('/midtrans-snap', function() {
//     $snapToken = "781c99c8-ede9-49ba-a356-599b48d86bec";
//     return view('midtrans-test.snap.order', compact('snapToken'));
// });

// Route Mail Activation
Route::get('email_verification/{token}', [EmailVerificationController::class, 'email_verification'])->name('email_verification');
