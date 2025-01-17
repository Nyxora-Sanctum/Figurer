<?php

namespace App\Http\Controllers\API;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
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

        // Attempt to authenticate the user with the provided credentials
        if (!auth()->attempt($request->only('username', 'password'))) {
            return response()->json([
                'message' => 'Invalid login details'
            ], 401);
        }

        return response()->json([
            'message' => 'Logged in successfully',
            'token' => auth()->user()->createToken('token')->plainTextToken // Return the token in the response
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
                'password' => 'required|min:8'
            ]);

            $user = User::create([
                'username' => $request->username,
                'email' => $request->email,
                'password' => bcrypt($request->password)
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