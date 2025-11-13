<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    // General login for doctor & manager
    public function login(Request $request)
    {
        $request->validate(['email'=>'required|email','password'=>'required']);
        if (!Auth::attempt($request->only('email','password'))) {
            return response()->json(['message'=>'Invalid credentials'], 401);
        }
        $user = Auth::user();
        if (!in_array($user->role, ['doctor','manager'])) {
            Auth::logout();
            return response()->json(['message'=>'Unauthorized role for this endpoint'], 403);
        }
        // session cookie set by Laravel
        return response()->json(['user'=>$user]);
    }

    // Separate patient login endpoint (uses same session approach)
    public function patientLogin(Request $request)
    {
        $request->validate(['email'=>'required|email','password'=>'required']);
        if (!Auth::attempt($request->only('email','password'))) {
            return response()->json(['message'=>'Invalid credentials'], 401);
        }
        $user = Auth::user();
        if ($user->role !== 'patient') {
            Auth::logout();
            return response()->json(['message'=>'Unauthorized role for this endpoint'], 403);
        }
        return response()->json(['user'=>$user]);
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    public function getUsersByRole(Request $request)
    {
        $role = $request->query('role');

        if (!$role) {
            return response()->json(['error' => 'Role parameter is required'], 400);
        }

        $users = \App\Models\User::where('role', $role)->get(['id', 'name', 'email', 'role']);

        return response()->json($users);
    }


    public function logout(Request $request)
    {
        // Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return response()->json(['message'=>'Logged out']);
    }
}
