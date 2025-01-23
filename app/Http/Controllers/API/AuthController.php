<?php

namespace App\Http\Controllers\API;
use App\Models\User;
use App\Models\Inventory;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{

    public function login(Request $request)
    {
        try {
            // Validate the incoming request data
            $request->validate([
                'username' => 'required|string',
                'password' => 'required'
            ]);

            // Get the user by username
            $user = User::where('username', $request->username)->first();

            // Check if the user exists
            if (!$user) {
                return response()->json([
                    'message' => 'Username not found'
                ], 401);
            }

            // Check if the password is correct
            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'message' => 'Incorrect password'
                ], 401);
            }

            return response()->json([
                'message' => 'Logged in successfully',
                'token' => $user->createToken('token')->plainTextToken // Return the token in the response
            ]);
        } catch (ValidationException $e) {
            // Handle validation exceptions
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            // Handle general exceptions
            return response()->json([
                'message' => 'An error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function logout(Request $request){
        try {
            auth()->user()->tokens()->delete();

            return response()->json([
                'message' => 'Logged out'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function register(Request $request)
    {
        try {
            $request->validate([
                'username' => 'required|string|unique:users,username',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:8',
                'gender' => 'nullable|string',
                'phone_number' => 'nullable|string',
            ]);

            $GeneratedUID = mt_rand(100000000000000, 999999999999999);
            $user = User::create([
                'id' => $GeneratedUID,
                'username' => $request->username,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'gender' => $request->gender,
                'phone_number' => $request->phone_number,
            ]);

            $inventory = Inventory::create([
                'id' => $GeneratedUID,
                'available_items' => json_encode([]),
                'used_items' => json_encode([]),
            ]);

            // Log in the user
            auth()->login($user);

            return response()->json([
                'message' => 'User created',
                'token' => $user->createToken('token')->plainTextToken
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}