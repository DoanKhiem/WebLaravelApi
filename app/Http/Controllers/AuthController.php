<?php

namespace App\Http\Controllers;

use App\Http\Requests\AgentRequest;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Đăng ký người dùng mới
    public function register(AgentRequest $request)
    {
        $validatedData = $request->validated();

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'message' => 'Register successfully'
        ]);
    }

    // Đăng nhập
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Invalid login details'
            ], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'message' => 'Login successfully',
            'user' => $user
        ]);
    }

    // Đăng xuất
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    public function checkToken(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->tokenCan('auth_token')) {
                return response()->json([
                    'status' => true,
                    'message' => 'Success'
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Error'
                ]);
            }
        } else {
            return response()->json([
                'message' => 'No user is currently logged in'
            ]);
        }

    }
}
