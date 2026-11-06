<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // STUDENT REGISTRATION
   
        public function register(Request $request)
        {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
                'role' => 'required|in:student,faculty',
                'year_level' => 'required_if:role,student|nullable|integer|between:1,4',
                'block' => 'required_if:role,student|nullable|string|max:30',
            ]);

            $user = new User();

            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->password = \Illuminate\Support\Facades\Hash::make(
                $data['password']
            );

            $user->role = $data['role'];

            if ($data['role'] === 'student') {
                $user->department = 'Information Technology';
                $user->year_level = $data['year_level'];
                $user->block = $data['block'];
                $user->approval_status = 'approved';
            } else {
                $user->department = 'Information Technology';
                $user->year_level = null;
                $user->block = null;
                $user->approval_status = 'pending';
            }

            $user->save();

            $message = $user->role === 'faculty'
                ? 'Registration submitted. Wait for faculty approval.'
                : 'Registration successful! Please login.';

            return response()->json([
                'message' => $message,
            ], 201);
        }

    // LOGIN FOR STUDENTS AND FACULTY
   
        public function login(Request $request)
        {
            $data = $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            $user = User::where('email', $data['email'])->first();

            if (!$user ||
                !\Illuminate\Support\Facades\Hash::check(
                    $data['password'],
                    $user->password
                )) {
                return response()->json([
                    'message' => 'Invalid email or password.',
                ], 401);
            }

           if (!in_array($user->role, ['student', 'faculty', 'admin'])) {
                return response()->json([
                    'message' => 'This account is not active.',
                ], 403);
            }

            if ($user->approval_status !== 'approved') {
                return response()->json([
                    'message' => 'Your faculty account is waiting for approval.',
                ], 403);
            }

            $token = $user->createToken('campusease')->plainTextToken;

            return response()->json([
                'message' => 'Login successful',
                'user' => $user,
                'token' => $token,
            ]);
        }

    // GET LOGGED-IN ACCOUNT
    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
        ]);
    }

    // LOGOUT
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout successful',
        ]);
    }

    
public function updateProfile(Request $request)
{
    $user = $request->user();

    if ($user->role !== 'student') {
        return response()->json([
            'message' => 'Only students can edit these details.',
        ], 403);
    }

    $data = $request->validate([
        'name' => 'required|string|max:255',
        'year_level' => 'required|integer|between:1,4',
        'block' => 'required|string|max:30',
    ]);

    $user->name = $data['name'];
    $user->department = 'Information Technology';
    $user->year_level = $data['year_level'];
    $user->block = $data['block'];
    $user->save();

    return response()->json([
        'message' => 'Profile updated successfully!',
        'user' => $user,
    ]);
}

}

//Update Profilee

