<?php

namespace App\Http\Controllers\Customer\Authentication;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Hash, Mail, Auth, DB};
use App\Http\Traits\HttpResponseTrait;
use Str;

use App\Mail\OTPMail;

use App\Models\User;
use App\Models\VerifyOTP;

class RequestOTPController extends Controller
{
    use HttpResponseTrait;

    public function requestOTP(Request $request) {
        try {
            $request->validate([
                'email' => 'required|email'
            ]);
    
            $otp = rand(100000, 999999);
            $email = $request->email;
    
            $user = User::where('email', $email)->first();
            
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => "User details does not exist on our database record!."
                ]);
            }
    
            // Delete all existing otp associated with the email
            VerifyOTP::where('email', $email)->delete();
            
            $getOTP = new VerifyOTP();
            $getOTP->email = $email;
            $getOTP->otp = $otp;
            $getOTP->save();
    
            $userEmail = $user->email;
            $userName = $user->first_name;
                    
            try {
                // Sending OTP via email
                Mail::to($user->email)->send(new OTPMail($userEmail, $otp, $userName));
                return response()->json([
                    'status' => true,
                    'message' => "Please check your email for verification OTP!."
                ]);             
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => "An error occurred while trying to send Mailing notification!",
                ], 200);
            }            

        } catch (Exception $e) {
            // Return an error response
            return $this->error([
                'message' => $e->getMessage(),
            ]);
        }
        
    }
}
