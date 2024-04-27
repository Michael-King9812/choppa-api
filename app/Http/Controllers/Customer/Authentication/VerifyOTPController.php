<?php

namespace App\Http\Controllers\Customer\Authentication;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Hash, Mail, Auth, DB};
use Illuminate\Support\Str;
use App\Http\Traits\HttpResponseTrait;

use Carbon\Carbon;
use App\Models\User;
use App\Models\VerifyOTP;


class VerifyOTPController extends Controller
{
    use HttpResponseTrait;

    public function index(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required'
        ]);

        try {            
            $checkEmailExistence = VerifyOTP::where('email', $request->email)->where('otp', $request->otp)->first();
            
            if (!$checkEmailExistence) {
                return response()->json([
                    "message" => "The credentials do not match our records!",
                ], 401);
            }

            // Check if OTP is expired
            if (Carbon::parse($checkEmailExistence->created_at)->addHours(3)->isPast()) {
                return response()->json([
                    "message" => "OTP has expired!",
                ], 401);
            }

            $user = User::where('email', $request->email)->first();
            $inviteCode = Str::random(10);

            if (!$user) {
                return response()->json([
                    "message" => "The credentials do not match our records!",
                ], 401);
            } else {
                $updateUser = \DB::table('users')->where('email', $request->email)->update([
                    'is_email_verified' => true,
                    'activity_status' => 1,
                    'invite_code' => $inviteCode,
                    'email_verified_at' => \Carbon\Carbon::now(),
                    'updated_at' => \Carbon\Carbon::now(),
                ]);
            }       

            // Delete all emails associated with this user on VerifyOTP table
            $deleteEmail = VerifyOTP::where('email', $request->email)->delete();

            if ($updateUser && $deleteEmail) {
                return response()->json([
                    'status' => true,
                    'message' => "Account verified successfully! Please Login.",
                ]);
            }
        } catch (Exception $e) {
            // Return an error response
            return $this->error([
                'message' => $e->getMessage(),
            ]);
        }
    }
}
