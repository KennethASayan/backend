<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\DMS\Users;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;


class DmsAuthController extends Controller
{
public function login(Request $request)
{
    $credentials = $request->validate([
        'department' => 'required|string',
        'password' => 'required|string',
    ]);

    $user = Users::where('department', $credentials['department'])->first();

    if (!$user) {
        return response()->json([
            'message' => 'Wrong Username or Password'
        ], 401);
    }

    if (Hash::check($credentials['password'], $user->password) ||
        $credentials['password'] === $user->password) {

        // Hash plain text passwords
        if ($credentials['password'] === $user->password) {
            $user->password = Hash::make($credentials['password']);
            $user->save();
        }

        // Log in the user using Laravel's session
        Auth::guard('third_db')->login($user, $request->get('rememberMe', false));
        $request->session()->regenerate();

        // Generate a simple token for frontend use (optional)
        $token = base64_encode($user->user_id . ':' . time());

        return response()->json([
            'user' => [
                'id' => $user->user_id,
                'department' => $user->department,
                'name' => $user->name,
                'user_dept' => $user->user_dept,
                'email' => $user->email,
                'status' => $user->status,
                'fullName' => $user->name,
                'division' => $user->user_dept,
            ],
            'token' => $token,
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

    public function me(Request $request)
{
    try {
        $user = Auth::guard('third_db')->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return response()->json([
            'user' => [
                'id' => $user->user_id,
                'department' => $user->department,
                'name' => $user->name,
                'user_dept' => $user->user_dept,
                'email' => $user->email,
                'status' => $user->status,
                'fullName' => $user->name,
                'division' => $user->user_dept,
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }
}

    public function user(Request $request)
    {
        return response()->json(Auth::guard('third_db')->user());
    }

    // New: Fetch all users for ManageUsers.vue
    public function getAllUsers()
    {
        $users = Users::select(
            'user_id as id',
            'name',
            'department',
            'user_dept as division',
            'email',
            'status'
        )
        ->orderBy('user_id', 'desc')
        ->get();

        return response()->json($users->map(function ($user) {
            return [
                'id' => $user->id,
                'fullName' => $user->name,
                'name' => $user->department,
                'email' => $user->email,
                'division' => $user->division,
                'status' => $user->status ? 'Activated' : 'Deactivated',
                'office' => [
                    'id' => $user->user_dept,
                    'name' => $user->division,
                    'code' => substr($user->division, 0, 3)
                ],
            ];
        }));
    }
public function createUser(Request $request)
{
    try {
        $validated = $request->validate([
            'fullName' => 'required|string',
            'name' => 'required|string|unique:pgsql_third.users,department',
            'email' => 'required|email|unique:pgsql_third.users,email',
            'password' => 'required|string|min:6', // Add password validation
            'division' => 'required|string',
            'status' => 'required|in:Activated,Deactivated',
        ], [
            // Custom validation messages
            'name.unique' => 'This username is already taken',
            'email.unique' => 'This email address is already registered',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least 6 characters'
        ]);

        $user = new Users();
        $user->name = $validated['fullName'];
        $user->department = $validated['name'];
        $user->email = $validated['email'];
        $user->password = Hash::make($validated['password']);
        $user->user_dept = $validated['division'];
        $user->status = $validated['status'] === 'Activated' ? 1 : 0;
        $user->save();

        return response()->json([
            'message' => 'User created successfully',
            'user' => $this->formatUserResponse($user)
        ], 201);
    } catch (ValidationException $e) {
        return response()->json([
            'message' => 'Validation failed',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        Log::error('User creation error: ' . $e->getMessage());
        return response()->json([
            'message' => 'Error creating user',
            'error' => $e->getMessage()
        ], 422);
    }
}

public function updateUser(Request $request, $id)
{
    try {
        $user = Users::findOrFail($id);

        $validated = $request->validate([
            'fullName' => 'required|string',
            'name' => 'required|string|unique:pgsql_third.users,department,' . $id . ',user_id',
            'email' => 'required|email|unique:pgsql_third.users,email,' . $id . ',user_id',
            'division' => 'required|string',
            'status' => 'required|in:Activated,Deactivated',
            'password' => 'nullable|string|min:6',
        ]);

        $user->name = $validated['fullName'];
        $user->department = $validated['name'];
        $user->email = $validated['email'];
        $user->user_dept = $validated['division'];
        $user->status = $validated['status'] === 'Activated' ? 1 : 0;

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return response()->json([
            'message' => 'User updated successfully',
            'type' => 'success',
            'user' => $this->formatUserResponse($user)
        ]);
    } catch (ValidationException $e) {
        return response()->json([
            'message' => 'Validation failed',
            'type' => 'error',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        Log::error('User update error: ' . $e->getMessage());
        return response()->json([
            'message' => 'Error updating user',
            'type' => 'error',
            'error' => $e->getMessage()
        ], 422);
    }
}

public function deleteUser($id)
{
    try {
        $user = Users::findOrFail($id);
        $userName = $user->name;
        $user->delete();

        return response()->json([
            'message' => 'Success',
            'description' => "User '$userName' has been deleted successfully",
            'type' => 'success'
        ]);
    } catch (\Exception $e) {
        Log::error('User deletion error: ' . $e->getMessage());
        return response()->json([
            'message' => 'Error',
            'description' => 'Failed to delete user',
            'type' => 'error'
        ], 422);
    }
}

public function resetPassword($id)
{
    try {
        $user = Users::findOrFail($id);
        $user->password = Hash::make('windows7');
        $user->save();

        return response()->json([
            'message' => 'Password reset successfully!',
            'type' => 'success',
            'description' => "Password has been reset to default (windows7)"
        ]);
    } catch (\Exception $e) {
        Log::error('Password reset error: ' . $e->getMessage());
        return response()->json([
            'message' => 'Error resetting password',
            'type' => 'error',
            'description' => $e->getMessage()
        ], 422);
    }
}

   private function formatUserResponse($user)
{
    return [
        'id' => $user->user_id,
        'fullName' => $user->name,
        'name' => $user->department,
        'email' => $user->email,
        'division' => $user->user_dept,
        'status' => $user->status ? 'Activated' : 'Deactivated',
        'office' => [
            'id' => $user->user_dept,
            'name' => $user->user_dept,
            'code' => substr($user->user_dept, 0, 3)
        ],
    ];
}
}
