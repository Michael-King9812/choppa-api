<?php

namespace App\Http\Controllers\Customer\Authentication;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Traits\HttpResponseTrait;

use Hash;

use App\Models\User;
use App\Models\ResetPasswordToken;

class ResetPasswordController extends Controller
{
    use HttpResponseTrait;

    public function resetPassword(Request $request) {
        try {
            $request->validate([
                'email' => 'required',
                'otp' => 'required',
                'password' => [
                    'required',
                    'min:8',
                ],
                'confirmPassword' => 'required|same:password',
            ]);
    
            $checkUserExistence = User::where('email', $request->email)->exists();

            // Check if a password reset token already exists for the given email address
            $verifyOTP = ResetPasswordToken::where('email', $request->email)->where('token', $request->otp)->exists(); 
            if (!$checkUserExistence) {
                return response()->json([
                    'error' => [
                        'message' => 'Email not found in our records.'
                    ]
                ], 400);
            }

            if (!$verifyOTP) {
                return response()->json([
                    'error' => [
                        'message' => 'Invalid OTP sent.'
                    ]
                ], 400);
            }
    
            $resetPassword = User::where('email', $request->email)->update([
                "password" => Hash::make($request->password),
            ]);

            if (!$resetPassword) {
                return response()->json([
                    'error' => [
                        'message' => 'Something went wrong!'
                    ]
                ], 400);
            }

            if ($verifyOTP) {
                // If a token already exists, delete it to generate a new one
                ResetPasswordToken::where('email', $request->email)->delete();
            }

            return response()->json([
                'status' => true,
                'message' => "Password reset successfully.",
            ]);

        } catch (\Exception $e) {
            return $this->error([
                'message' => $e->getMessage(),
            ]);
        }
    }
}
