<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\DMS\Users;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class DmsAuthController extends Controller
{

public function forgotpassword(Request $request)
{
    try {
        // Validate email
        $request->validate([
            'email' => 'required|email|exists:pgsql_third.users,email',
        ]);

        $token = Str::random(64);
        $email = $request->email;

        // Delete any existing reset tokens for this email
        DB::connection('pgsql_third')->table('password_resets')
            ->where('email', $email)
            ->delete();

        // Create new reset token
        DB::connection('pgsql_third')->table('password_resets')->insert([
            'email' => $email,
            'token' => $token,
            'created_at' => Carbon::now()
        ]);

        // Send email
        Mail::send('emails.forgot-password', ['token' => $token], function($message) use($email) {
            $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
            $message->to($email);
            $message->subject('Reset Password Notification');
        });

        return response()->json([
            'message' => 'Password reset link has been sent to your email',
            'status' => 'success'
        ]);

    } catch (\Exception $e) {
        \Log::error('Password reset error: ' . $e->getMessage());
        return response()->json([
            'message' => 'Failed to process password reset request',
            'status' => 'error',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function resetPasswords(Request $request)
{
    try {
        $request->validate([
            'email' => 'required|email|exists:pgsql_third.users,email',
            'password' => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required',
            'token' => 'required'
        ]);

        // Check if token exists and is valid
        $resetRecord = DB::connection('pgsql_third')
            ->table('password_resets')
            ->where([
                'email' => $request->email,
                'token' => $request->token
            ])
            ->first();

        if (!$resetRecord) {
            return response()->json([
                'message' => 'Invalid reset token',
                'status' => 'error'
            ], 400);
        }

        // Check if token is expired (60 minutes)
        if (Carbon::parse($resetRecord->created_at)->addMinutes(60)->isPast()) {
            return response()->json([
                'message' => 'Reset token has expired',
                'status' => 'error'
            ], 400);
        }

        // Update password
        DB::connection('pgsql_third')
            ->table('users')
            ->where('email', $request->email)
            ->update(['password' => Hash::make($request->password)]);

        // Delete reset token
        DB::connection('pgsql_third')
            ->table('password_resets')
            ->where('email', $request->email)
            ->delete();

        return response()->json([
            'message' => 'Password has been reset successfully',
            'status' => 'success'
        ]);

    } catch (\Exception $e) {
        \Log::error('Password reset error: ' . $e->getMessage());
        return response()->json([
            'message' => 'Failed to reset password',
            'status' => 'error',
            'error' => $e->getMessage()
        ], 500);
    }
}

    /**
     * Handle user login and generate a Sanctum token.
     */
public function login(Request $request)
{
    try {
        $credentials = $request->validate([
            'department' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = Users::where('department', $credentials['department'])->first();

        // Check if user exists and the password matches the hashed password
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            \Log::info('Invalid credentials');
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Create new token
        $token = $user->createToken('auth-token')->plainTextToken;

        \Log::info('Login successful');

        return response()->json([
            'user' => $this->formatUserResponse($user),
            'token' => $token,
            'message' => 'Login successful'
        ]);

    } catch (\Exception $e) {
        \Log::error('Login error: ' . $e->getMessage());
        return response()->json([
            'message' => 'Login failed',
            'error' => $e->getMessage()
        ], 500);
    }
}

    /**
     * Log the user out by revoking their tokens.
     */
    public function logout(Request $request)
    {
        try {
            $request->user()->tokens()->delete();
            return response()->json(['message' => 'Logged out successfully']);
        } catch (\Exception $e) {
            Log::error('Logout error: ' . $e->getMessage());
            return response()->json(['message' => 'Logout failed'], 500);
        }
    }

    /**
     * Get the authenticated user's details.
     */
  // controller/DmsAuthController.php

 public function me(Request $request)
    {
        try {
            // Log the incoming request for debugging
            \Log::info('ME endpoint called', [
                'headers' => $request->headers->all(),
                'bearer_token' => $request->bearerToken() ? 'Token present' : 'No token'
            ]);

            // Check if user is authenticated via Sanctum
            $user = $request->user();
            
            if (!$user) {
                \Log::warning('ME endpoint: No authenticated user found');
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            // Verify the user still exists in database
            $dbUser = Users::find($user->user_id);
            if (!$dbUser) {
                \Log::warning('ME endpoint: User not found in database', ['user_id' => $user->user_id]);
                return response()->json(['message' => 'User not found'], 404);
            }

            // Log successful authentication
            \Log::info('ME endpoint: User authenticated successfully', [
                'user_id' => $user->user_id,
                'department' => $user->department,
                'name' => $user->name
            ]);

            return response()->json([
                'user' => $this->formatUserResponse($user),
                'message' => 'User data retrieved successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('ME endpoint error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'message' => 'Internal server error',
                'error' => config('app.debug') ? $e->getMessage() : 'Authentication failed'
            ], 500);
        }
    }



    /**
     * Fetch all users.
     */
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
        $user->password = Hash::make('Windows7');
        $user->save();

        return response()->json([
            'message' => 'Password reset successfully!',
            'type' => 'success',
            'description' => "Password has been reset to default (Windows7)"
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
