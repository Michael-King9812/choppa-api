<?php

namespace App\Http\Controllers\Customer\Authentication;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Hash, Auth, Mail};
use App\Http\Traits\HttpResponseTrait;

use Str;

use App\Models\User;
use App\Models\ResetPasswordToken;

use App\Mail\ResetPasswordMail;

class ForgotPasswordController extends Controller
{
    use HttpResponseTrait;

    public function forgotPassword(Request $request) {
        try {
            $request->validate([
                'email' => 'required|email',
            ]);
            // Check if the user exists
            $userExists = User::where('email', $request->email)->exists();
    
            if (!$userExists) {
                return response()->json([
                    'error' => [
                        'message' => 'Email not found in our records.'
                    ]
                ], 400);
            }

            // Generate a new token
            $otp = rand(100000, 999999);
    
            // Check if a password reset token already exists for the given email address
            $existingToken = ResetPasswordToken::where('email', $request->email)->first();
    
            if ($existingToken) {
                // If a token already exists, delete it to generate a new one
                $existingToken->delete();
            }
            
            // Create a new password reset token
            $forgotPassword = \DB::table('reset_password_tokens')->insert([
                'email' => $request->email,
                'token' => $otp,
                'created_at' => \Carbon\Carbon::now(),
            ]);
            $email = $request->email;
            $otp = $otp;
            
            try {
                Mail::to($request->email)->send(new ResetPasswordMail($email, $otp));                
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => "An error occurred while trying to send Mailing notification!",
                    'error' => $e->message
                ], 200);
            }
           
    
            return response()->json([
                'status' => true,
                'message' => "Forgot password request sent successfully. Please check your email.",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
