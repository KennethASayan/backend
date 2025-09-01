<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DmsAuthController extends Controller
{
    public function login(Request $request)
{
    $credentials = $request->validate([
        'department' => 'required|string',
        'password' => 'required|string',
    ]);
    \Log::info('Login attempt:', $credentials);

    $user = \App\DMS\Users::where('department', $credentials['department'])->first();

    if ($user && $user->password === $credentials['password']) {
        // Manually log in the user
        Auth::guard('third_db')->login($user);

        $request->session()->regenerate();

        return response()->json([
            'user' => [
                'id' => $user->user_id,
                'department' => $user->department,
                'name' => $user->name,
                'user_dept' => $user->user_dept,
            ],
            'message' => 'Login successful',
        ]);
    }

    return response()->json([
        'message' => 'Wrong Username or Password'
    ], 401);
}

    public function logout(Request $request)
    {
        Auth::guard('third_db')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function user(Request $request)
    {
        return response()->json(Auth::guard('third_db')->user());
    }
}
