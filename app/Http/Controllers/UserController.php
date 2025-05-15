<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class UserController extends Controller
{
    /**
     * Display a listing of users
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            // You might want to limit this or add pagination in a real app
            $users = User::select(['id', 'username', 'avatar', 'level', 'score'])
                ->get();
                
            return response()->json([
                'success' => true,
                'users' => $users
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve users: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user profile
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProfile(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'success' => true,
            'user' => [
                'username' => $user->username,
                'avatar' => $user->avatar,
                'level' => $user->level,
                'score' => $user->score
            ]
        ]);
    }

    /**
     * Update user avatar
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateAvatar(Request $request)
    {
        try {
            $user = $request->user();
            
            // If simple avatar path update
            if ($request->has('avatar') && !$request->has('custom_avatar')) {
                $user->avatar = $request->input('avatar');
                $user->save();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Avatar updated successfully',
                    'user' => [
                        'username' => $user->username,
                        'avatar' => $user->avatar,
                        'level' => $user->level,
                        'score' => $user->score
                    ]
                ]);
            }
            
            // Handle custom avatar update
            return response()->json([
                'success' => false,
                'message' => 'No avatar provided'
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update avatar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user progress (level and score)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProgress(Request $request)
    {
        try {
            $user = $request->user();
            
            if ($request->has('level')) {
                $user->level = $request->input('level');
            }
            
            if ($request->has('score')) {
                $user->score = $request->input('score');
            }
            
            $user->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Progress updated successfully',
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
                'message' => 'Failed to update progress: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Change user password
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!Hash::check($request->input('current_password'), $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect'
                ], 400);
            }
            
            $user->password = Hash::make($request->input('new_password'));
            $user->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Password changed successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to change password: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available avatars
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAvailableAvatars()
    {
        try {
            $defaultAvatars = [
                'assets/avatars/avatar1.png',
                'assets/avatars/avatar2.png',
                'assets/avatars/avatar3.png',
                'assets/avatars/avatar4.png',
            ];
            
            // You could also fetch from storage or database if needed
            // $customAvatars = Storage::files('public/avatars');
            // $avatarUrls = array_map(function($path) {
            //     return url(Storage::url($path));
            // }, $customAvatars);
            // 
            // $allAvatars = array_merge($defaultAvatars, $avatarUrls);
            
            return response()->json([
                'success' => true,
                'avatars' => $defaultAvatars,
                'defaultAvatar' => 'assets/avatars/default.png'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get avatars: ' . $e->getMessage()
            ], 500);
        }
    }
}