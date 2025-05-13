<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    private $apiUrl = 'http://localhost:5000/api';

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $response = Http::post($this->apiUrl.'/login', [
            'email' => $request->email,
            'password' => $request->password
        ]);

        if ($response->successful()) {
            $data = $response->json();
            session([
                'jwt_token' => $data['token'],
                'user_email' => $data['user']['email']
            ]);
            return redirect('/welcome');
        }

        return back()->withErrors(['message' => 'Login failed']);
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6'
        ]);

        $response = Http::post($this->apiUrl.'/register', [
            'email' => $request->email,
            'password' => $request->password
        ]);

        if ($response->successful()) {
            return redirect('/login')->with('success', 'Registration successful');
        }

        return back()->withErrors(['message' => 'Registration failed']);
    }

    public function profile()
    {
        if (!session('jwt_token')) {
            return redirect('/login');
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . session('jwt_token')
        ])->get($this->apiUrl.'/profile');

        if ($response->successful()) {
            return view('auth.profile', [
                'user' => $response->json(),
                'email' => session('user_email')
            ]);
        }

        return redirect('/login')->withErrors(['message' => 'Please login']);
    }

    public function logout()
    {
        session()->forget(['jwt_token', 'user_email']);
        return redirect('/login');
    }
}