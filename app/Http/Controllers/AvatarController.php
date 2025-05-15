<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

class AvatarController extends Controller
{
    public function show($filename)
    {
        $path = storage_path('app/public/avatars/' . $filename);
        
        if (!File::exists($path)) {
            return response()->json(['error' => 'Avatar not found'], 404);
        }
        
        $file = File::get($path);
        $type = File::mimeType($path);
        
        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);
        $response->header('Access-Control-Allow-Origin', '*');
        $response->header('Cache-Control', 'public, max-age=86400');
        
        return $response;
    }
}