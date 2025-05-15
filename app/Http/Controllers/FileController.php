<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileController extends Controller
{
    /**
     * Upload avatar file
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadAvatar(Request $request)
    {
        try {
            // Handle base64 upload
            if ($request->has('custom_avatar') && $request->has('avatar_data')) {
                $imageData = $request->input('avatar_data');
                $imageName = $request->input('avatar_name', 'custom_avatar.png');
                
                // Generate a unique name
                $fileName = 'avatar_' . Str::uuid() . '.' . pathinfo($imageName, PATHINFO_EXTENSION);
                
                // Decode base64 data
                $imageData = base64_decode($imageData);
                
                // Save to storage without image processing
                $path = 'public/avatars/' . $fileName;
                Storage::put($path, $imageData);
                
                // Return the direct API URL instead of storage URL
                $url = url('api/avatars/' . $fileName);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Avatar uploaded successfully',
                    'avatar_url' => $url
                ]);
            }
            
            // Handle multipart file upload
            if ($request->hasFile('avatar_file')) {
                $file = $request->file('avatar_file');
                
                // Validate file
                if (!$file->isValid()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid file upload'
                    ], 400);
                }
                
                // Generate a unique name
                $fileName = 'avatar_' . Str::uuid() . '.' . $file->getClientOriginalExtension();
                
                // Save file directly without processing
                $path = $file->storeAs('public/avatars', $fileName);
                
                // Return the direct API URL instead of storage URL
                $url = url('api/avatars/' . $fileName);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Avatar uploaded successfully',
                    'avatar_url' => $url
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'No file provided'
            ], 400);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error uploading avatar: ' . $e->getMessage()
            ], 500);
        }
    }
}