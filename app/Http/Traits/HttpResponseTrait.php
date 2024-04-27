<?php

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse; 

trait HttpResponseTrait 
{
    /**
     * Return a success response.
     *
     * @param array $data
     * @param int $statusCode
     * @return JsonResponse
     */
    protected function success(array $data,  $statusCode = 200): JsonResponse
    {
        return response()->json(['status' => true, 'data' => $data], $statusCode);
    }

    /**
     * Return an error response.
     *
     * @param string $message
     * @param int $statusCode
     * @return JsonResponse
     */
    protected function error($message = 'An error occurred', $statusCode = 500): JsonResponse
    {
        return response()->json(['status' => false, 'error' => $message], $statusCode);
    }

    /**
     * Return an authentication error response.
     *
     * @param string $message
     * @param int $statusCode
     * @return JsonResponse
     */
    protected function authError($message = 'incorrect credentials', $statusCode = 401): JsonResponse
    {
        return response()->json(['status' => false, 'error' => $message], $statusCode);
    }

    /**
     * Return a validation error response.
     *
     * @param array $errors
     * @return JsonResponse
     */
    protected function validationError(array $errors): JsonResponse
    {
        return response()->json(['status' => false, 'errors' => $errors], 422);
    }

    /**
     * Authenticate the user with the given credentials.
     *
     * @param array $credentials
     * @return JsonResponse
     */
    protected function authenticate(array $credentials): JsonResponse
    {
        if (!Auth::attempt($credentials)) {
            return $this->authError();
        }

        $user = Auth::user();
        $token = $user->createToken('API Token')->plainTextToken;

        return response()->json([
            'status' => true,
            'user' => $user,
            'role' =>$user->roles->role,
            'token' => $token,
            'message' => "Login successful"
        ]);
    }
}