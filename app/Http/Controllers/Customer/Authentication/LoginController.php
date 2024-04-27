<?php

namespace App\Http\Controllers\Customer\Authentication;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Hash, Mail, Auth, DB, Crypt};
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Http\Traits\HttpResponseTrait;

use App\Http\Requests\LoginUserRequest;
use App\Http\Resources\UserResource;

use App\Models\User;

class LoginController extends Controller
{
    function index(LoginUserRequest $request) {
        
        // Validates all requests sent
        $request->validated($request->all());
        // Check User existence
        $user = User::where('email', $request->email)->first();

        // Check if entered password matched
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                "message" => "The credentials do not match our records!",
            ], 400);
        }

        try {
            // Authenticate user
            Auth::attempt(['email' => $request->email, 'password' => $request->password]);

            // Checks user verification status
            if ($user->is_email_verified == false) {
                return response()->json([
                    "message" => "User account not verified yet!",
                ], 200);
            }

            // Check if user is Suspended
            if ($user->activity_status == 2) {
                return response()->json([
                    "message" => "User Account has been suspended!",
                ], 401);
            }

            // Check if user is Inactive
            if ($user->activity_status == 3) {
                return response()->json([
                    "message" => "User Account is currently inactive!",
                ], 401);
            }

            // Create Token for user
            $token = $user->createToken("API Token Of " . $user->email)->plainTextToken;

            $response = [
                "message"=>"Login Successful",
                "data"=> new UserResource($user),
                "token"=>$token
            ];

            // return response
            return response()->json($response, 200);
        
        } catch (\Exception $e) {
            return $this->error([
                'message' => $e->getMessage(),
            ]);
        }
    }
     
    public function logout() 
    {
        // Check if the user is authenticated
        if (Auth::guard('sanctum')->check()) {
            // Get the authenticated admin user
            $user = Auth::guard('sanctum')->user();
            // Revoke the token on logout
            
            $user->tokens()->delete();
            
            // Return a JSON response for successful logout
            return response()->json(['message' => 'Logout Successful']);
        }
        
        // Return a JSON response indicating that the user is not logged in
        return response()->json(['message' => 'Customer is not logged in'], 401);
    }
}
