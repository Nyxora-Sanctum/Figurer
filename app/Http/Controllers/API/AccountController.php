<?php
namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Accounts;
use App\Models\inventory;

class AccountController extends Controller
{   
    // CRUD User
    public function getCurrentProfile(Request $request)
    {
        $user = auth()->user();
        $profile = $user->only(['id','username', 'email', 'gender', 'phone_number']);
        return response()->json($profile);
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        $user->update($request->only(['username', 'email', 'gender', 'phone_number']));
        return response()->json(['message' => 'Profile updated successfully']);
    }


    public function deleteAccount(Request $request, $id)
    {
        $account = Accounts::find($id);

        if (!$account) {
            return response()->json(['message' => 'Account not found'], 404);
        }

        $account->delete();

        return response()->json(['message' => 'Account deleted successfully']);
    }

    function getTotalUsers(Request $request)
    {
        $usersPerDay = Accounts::where('role', 'user')
            ->selectRaw('COUNT(*) as count, DAY(created_at) as day')
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('count')
            ->toArray();

        $totalUsers = array_sum($usersPerDay);

        return response()->json([
            'per_day' => $usersPerDay,
            'total' => $totalUsers
        ]);
    }

    function getNewUsers(Request $request, $latestcount)
    {
        $newUsers = Accounts::where('role', 'user')
            ->orderBy('created_at', 'desc')
            ->take($latestcount)
            ->get();

        return response()->json($newUsers);
    }

    public function getAllAccounts()
    {
        $accounts = Accounts::all();
        return $accounts;
    }

    public function getAccountById($id)
    {
        $account = Accounts::find($id);

        if (!$account) {
            return response()->json(['message' => 'Account not found'], 404);
        }

        return $account;
    }
    
    public function updateProfileAdmin(Request $request, $id)
    {
        // Log the raw request payload
        \Log::info('Raw Request Payload: ' . json_encode($request->all()));

        $user = Accounts::find($id);

        if (!$user) {
            \Log::error('User not found');
            return response()->json(['message' => 'User not found'], 404);
        }

        try {
            $validatedData = $request->validate([
                'username' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'gender' => 'required|string|in:male,female,other',
                'phone_number' => 'required|string|max:15',
                'address' => 'nullable|string|max:500',
            ]);

            $user->update($validatedData);

            \Log::info('Profile updated successfully');
            \Log::info('User Data after update: ' . json_encode($user));

            return response()->json([
                'message' => 'Profile updated successfully',
                'user' => $user,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to update profile: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to update profile'], 500);
        }
    }
}