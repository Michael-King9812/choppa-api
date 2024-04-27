<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Customer\{
    Authentication\RegistrationController,
    Authentication\LoginController,
    Authentication\VerifyOTPController,
    Authentication\ForgotPasswordController,
    Authentication\ResetPasswordController,
    Authentication\RequestOTPController,
};
use App\Http\Controllers\Customer\CustomerDeliveryAddressController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// User Authentication
Route::post('/register', [RegistrationController::class, 'index']);
Route::post('/register/{referralCode}', [RegistrationController::class, 'index']);
Route::post('/login', [LoginController::class, 'index']);
Route::post('/forgot-password', [ForgotPasswordController::class, 'forgotPassword']);
Route::patch('/reset-password', [ResetPasswordController::class, 'resetPassword']);
Route::post('/logout', [LoginController::class, 'logout']);
Route::post('/verify/otp', [VerifyOTPController::class, 'index']);
Route::post('/request/otp', [RequestOTPController::class, 'requestOTP']);

Route::middleware('auth:sanctum')->prefix('customer')->group(function() {
    Route::controller(CustomerDeliveryAddressController::class)->group(function() {        
        Route::post('/address', 'createAddress');
        Route::get('/latest-address', 'getLatestAddressForUser');
    });
});
