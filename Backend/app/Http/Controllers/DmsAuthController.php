<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\DMS\Users;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;


class DmsAuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'department' => 'required|string',
            'password' => 'required|string',
        ]);
        \Log::info('Login attempt:', $credentials);

        $user = Users::where('department', $credentials['department'])->first();

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
                    'email' => $user->email,
                    'status' => $user->status,
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
        )->get();

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
                'userRole' => 'Staff' // Add logic to determine role
            ];
        }));
    }

    public function createUser(Request $request)
    {
        $validated = $request->validate([
            'fullName' => 'required|string',
            'name' => 'required|string|unique:users,department',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'division' => 'required|string',
            'status' => 'required|in:Activated,Deactivated',
        ]);

        $user = new Users();
        $user->name = $validated['fullName'];
        $user->department = $validated['name'];
        $user->email = $validated['email'];
        $user->password = $validated['password']; // Consider hashing
        $user->user_dept = $validated['division'];
        $user->status = $validated['status'] === 'Activated' ? 1 : 0;
        $user->save();

        return response()->json([
            'message' => 'User created successfully',
            'user' => $this->formatUserResponse($user)
        ]);
    }

    public function updateUser(Request $request, $id)
    {
        $user = Users::findOrFail($id);

        $validated = $request->validate([
            'fullName' => 'sometimes|string',
            'name' => 'sometimes|string|unique:users,department,' . $id . ',user_id',
            'email' => 'sometimes|email|unique:users,email,' . $id . ',user_id',
            'division' => 'sometimes|string',
            'status' => 'sometimes|in:Activated,Deactivated',
        ]);

        if (isset($validated['fullName'])) $user->name = $validated['fullName'];
        if (isset($validated['name'])) $user->department = $validated['name'];
        if (isset($validated['email'])) $user->email = $validated['email'];
        if (isset($validated['division'])) $user->user_dept = $validated['division'];
        if (isset($validated['status'])) $user->status = $validated['status'] === 'Activated' ? 1 : 0;

        $user->save();

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $this->formatUserResponse($user)
        ]);
    }

    public function deleteUser($id)
    {
        $user = Users::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }

     public function resetPassword($id)
    {
        $user = Users::findOrFail($id);
        // Set default password to "windows7" and hash it
        $user->password = password_hash('windows7', PASSWORD_BCRYPT, [
            'cost' => 10,
        ]);
        $user->save();

        return response()->json(['message' => 'Password reset successfully to default']);
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
            'userRole' => 'Staff' // Add logic to determine role
        ];
    }
}
