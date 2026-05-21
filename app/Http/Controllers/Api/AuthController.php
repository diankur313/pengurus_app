<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Handle mobile app login and return Sanctum token.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kredensial tidak valid.',
            ], 401);
        }

        $user = User::where('email', $request->email)->firstOrFail();

        // Optional: Check if user has teacher/admin role if needed
        // if (!$user->hasRole(['super_admin', 'pengurus'])) { ... }

        $token = $user->createToken('mobile-scanner-token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Login berhasil',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'photo' => $user->getFilamentAvatarUrl(),
                ],
                'token' => $token,
            ]
        ]);
    }

    /**
     * Handle logout.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logout berhasil',
        ]);
    }
}
