<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index()
    {
        $users = User::orderBy('created_at', 'desc')->get();
        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:30|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'avatar' => 'nullable|string',
            'custom_avatar' => 'nullable|boolean',
            'avatar_file' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'level' => 'nullable|integer|min:1',
            'score' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->route('users.create')
                ->withErrors($validator)
                ->withInput();
        }

        $avatarPath = $request->input('avatar', 'assets/avatars/default.png');

        // Handle custom avatar file upload
        if ($request->has('custom_avatar') && $request->hasFile('avatar_file')) {
            $file = $request->file('avatar_file');
            $fileName = 'avatar_' . Str::uuid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/avatars', $fileName);
            $avatarPath = url('api/avatars/' . $fileName);
        }

        $user = User::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'avatar' => $avatarPath,
            'level' => $request->input('level', 1),
            'score' => $request->input('score', 0),
        ]);

        return redirect()->route('users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:30|unique:users,username,' . $user->id,
            'avatar' => 'nullable|string',
            'custom_avatar' => 'nullable|boolean',
            'avatar_file' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'level' => 'nullable|integer|min:1',
            'score' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->route('users.edit', $user->id)
                ->withErrors($validator)
                ->withInput();
        }

        $avatarPath = $request->input('avatar', $user->avatar);

        // Handle custom avatar file upload
        if ($request->has('custom_avatar') && $request->hasFile('avatar_file')) {
            $file = $request->file('avatar_file');
            $fileName = 'avatar_' . Str::uuid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/avatars', $fileName);
            $avatarPath = url('api/avatars/' . $fileName);
        }

        $user->update([
            'username' => $request->username,
            'avatar' => $avatarPath,
            'level' => $request->input('level', $user->level),
            'score' => $request->input('score', $user->score),
        ]);

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Change user password.
     */
    public function changePassword(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->route('users.edit', $user->id)
                ->withErrors($validator)
                ->withInput();
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return redirect()->route('users.index')
            ->with('success', 'Password changed successfully.');
    }
}
