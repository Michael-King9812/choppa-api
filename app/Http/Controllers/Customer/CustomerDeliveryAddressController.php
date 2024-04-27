<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Auth;

use App\Models\User;
use App\Models\CustomerDeliveryAddress;

class CustomerDeliveryAddressController extends Controller
{
    /**
     * Create a new address for the user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function createAddress(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'address' => 'required|string|max:255',
        ]);
        
        // Create a new address instance
        $address = new CustomerDeliveryAddress([
            'user_id' => Auth::user()->id,
            'address' => $validatedData['address'],
        ]);

        // Save the address for the user
        $address->save();

        return response()->json([
            'status' => true,
            'message' => "Address added successfully",
        ], 201);
        return response()->json(['message' => 'Address added successfully'], 201);
    }

    public function getLatestAddressForUser() {
        try {
            // Get the latest added address for the user
            $latestAddress = CustomerDeliveryAddress::where('user_id', Auth::user()->id)->latest()->first();

            return response()->json([
                'status' => true,
                'message' => "Latest customer address gotten successfully!",
                'data' => $latestAddress
            ]);
        } catch (Exception $e) {
            return $this->error([
                'message' => $e->getMessage(),
            ]);
        }

    }
}
