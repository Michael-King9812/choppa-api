<?php

namespace App\Http\Controllers\Customer\Authentication;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Hash, Mail, Auth, DB};
use Illuminate\Database\Eloquent\SoftDeletes;
use Str;
use App\Http\Traits\HttpResponseTrait;

use App\Mail\OTPMail;


use App\Http\Resources\UserResource;

use App\Models\User;
use App\Models\VerifyOTP;

class RegistrationController extends Controller
{
    use HttpResponseTrait;

    public function index(Request $request, $referralCode = null) {
        $otp = rand(100000, 999999);

        if ($referralCode) {
            // Find the referrer based on the referral code
            $referrer = User::where('invite_code', $referralCode)->select('invite_code')->first();
            
            if (!$referrer) {
                return response()->json([
                    'status' => false,
                    'message' => 'User with referral code does not exist!'
                ], 400);
            }
        }

        try {           
            // Check if soft deleted user exists
            $checkUser = User::withTrashed()->where('email', $request->email)->first();
           
            // If soft deleted user exists
            if ($checkUser) {
                // If the user's email is not verified
                if ($checkUser->is_email_verified == '0') {
                    // Restore the soft deleted user
                    $checkUser->restore();
                    
                    // Delete all existing tokens associated with the email
                    VerifyOTP::where('email', $request->email)->delete();
                    
                    // Generate new token
                    $getOtp = new VerifyOTP();
                    $getOtp->email = $request->email;
                    $getOtp->otp = $otp;
                    $getOtp->save();

                    $userEmail = $checkUser->email;
                    $userName = $checkUser->first_name;
                
                    try {
                        // Sending OTP via email
                        Mail::to($request->email)->send(new OTPMail($userEmail, $otp, $userName));   
                        return response()->json([
                            'status' => true,
                            'message' => "User with email already exist but not verified! Please check your email for verification.",
                        ], 201);           
                    } catch (\Exception $e) {
                        return response()->json([
                            'status' => false,
                            'message' => "An error occurred while trying to send Mailing notification!",
                        ], 200);
                    }
                    
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'User with email already exists and is verified!',
                    ], 400);
                }
            }

            // Validate all requested data
            $request->validate([
                'firstName' => 'required',
                'lastName' => 'required',
                'email' => 'required|email|unique:users,email',
                'phone' => 'required',
                'password' => [
                    'required',
                    'min:8',
                ],
                'confirmPassword' => 'required|same:password',
            ]);
            
            // Create a new user
            $user = User::create([
                'first_name' => $request->firstName,
                'last_name' => $request->lastName,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'referral' => $request->referralCode,
                'remember_token' => Str::random(100),
            ]);

            $checkVerifyOtp = VerifyOTP::where('email', $request->email);
            if ($checkVerifyOtp->exists()) {
                // Delete all existing tokens associated with the email
                VerifyOTP::where('email', $request->email)->delete();
            }            
            
            // Generate new token
            $getOtp = new VerifyOTP();
            $getOtp->email = $request->email;
            $getOtp->otp = $otp;
            $getOtp->save();
            
            $userEmail = $user->email;
            $userName = $user->first_name . " " . $user->last_name;

            try {
                // Sending OTP via email
                Mail::to($request->email)->send(new OTPMail($userEmail, $otp, $userName));              
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => "An error occurred while trying to send Mailing notification!",
                ], 200);
            }            

            if ($user) {
                return response()->json([
                    'status' => true,
                    'message' => "User created successfully. Verify Email.",
                    'data' => new UserResource($user),
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Something went wrong!',
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
