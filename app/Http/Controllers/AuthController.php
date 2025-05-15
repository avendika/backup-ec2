<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    /**
     * Register a new user
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'username' => 'required|string|max:100|unique:users',
                'password' => 'required|string|min:6',
                'avatar' => 'nullable|string',
                'custom_avatar' => 'nullable|boolean',
                'avatar_data' => 'nullable|string',
                'avatar_name' => 'nullable|string',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }
            
            // Determine avatar path
            $avatarPath = $request->input('avatar', 'assets/avatars/default.png');
            
            // Handle custom base64 avatar
            if ($request->has('custom_avatar') && $request->has('avatar_data')) {
                $imageData = base64_decode($request->input('avatar_data'));
                $imageName = $request->input('avatar_name', 'custom_avatar.png');
                
                // Generate unique filename
                $fileName = 'avatar_' . Str::uuid() . '.' . pathinfo($imageName, PATHINFO_EXTENSION);
                
                // Save to storage
                $path = 'public/avatars/' . $fileName;
                Storage::put($path, $imageData);
                
                // Get direct API URL for the avatar (instead of storage URL)
                $avatarPath = url('api/avatars/' . $fileName);
            }
            
            // Create user
            $user = User::create([
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'avatar' => $avatarPath,
                'level' => 1,
                'score' => 0
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'user' => [
                    'username' => $user->username,
                    'avatar' => $user->avatar,
                    'level' => $user->level,
                    'score' => $user->score
                ]
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Login user and create token
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        try {
            $credentials = $request->only('username', 'password');
            
            if (!Auth::attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials'
                ], 401);
            }
            
            $user = Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken;
            
            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'token' => $token,
                'user' => [
                    'username' => $user->username,
                    'avatar' => $user->avatar,
                    'level' => $user->level, 
                    'score' => $user->score
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Login failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Logout user (revoke token)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Logout successful'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout failed: ' . $e->getMessage()
            ], 500);
        }
    }
}