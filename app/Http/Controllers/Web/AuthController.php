<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Tampilkan halaman login
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Proses login
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $username = $request->input('username');
        $password = $request->input('password');

        // Cek admin login dengan data dummy
        if ($username === 'admin' && $password === 'admin123') {
            // Login sebagai admin (buat session admin)
            session([
                'admin_logged_in' => true,
                'user_type' => 'admin',
                'username' => 'admin'
            ]);
            
            return redirect()->route('admin.home')->with('success', 'Login admin berhasil!');
        }

        // Cek login user biasa
        $user = User::where('username', $username)->first();
        
        if ($user && Hash::check($password, $user->password)) {
            Auth::login($user);
            return redirect()->route('user.profile')->with('success', 'Login berhasil!');
        }

        return back()->withErrors([
            'login' => 'Username atau password salah.'
        ])->withInput();
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        if (session('admin_logged_in')) {
            session()->forget(['admin_logged_in', 'user_type', 'username']);
            return redirect()->route('login')->with('success', 'Logout admin berhasil!');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Logout berhasil!');
    }

    /**
     * Tampilkan profil user
     */
    public function profile()
    {
        $user = Auth::user();
        return view('user.profile', compact('user'));
    }
}