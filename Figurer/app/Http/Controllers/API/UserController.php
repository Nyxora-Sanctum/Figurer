<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{   
    public function getCurrentProfile(Request $request)
    {
        $user = auth()->user();
        $profile = $user->only(['username', 'email']);
        return response()->json($profile);
    }
    
    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        $user->update($request->only(['username', 'email']));
        return response()->json(['message' => 'Profile updated successfully']);
    }
}

