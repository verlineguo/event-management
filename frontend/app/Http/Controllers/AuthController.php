<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
            'password' => 'required',
        ]);

        $response = Http::post($this->apiUrl . '/login', [
            'email' => $request->email,
            'password' => $request->password,
        ]);

        if ($response->successful()) {
            $data = $response->json();

            session([
                'jwt_token' => $data['token'],
                'user_email' => $data['user']['email'],
                'user_role_id' => $data['user']['role_id'],
                'user_role_name' => $data['user']['role_name'],
            ]);

            // Periksa apakah role_id valid untuk redirect
            if (isset($data['user']['role_name'])) {
                return $this->redirectToDashboard($data['user']['role_name']);
            } else {
                Log::error('Role ID missing in user data');
                return redirect('/welcome');
            }
        } else {
            Log::error('Login API failed', ['status' => $response->status(), 'response' => $response->body()]);
            return back()->withErrors(['message' => 'Login failed: ' . ($response->json()['error'] ?? 'Unknown error')]);
        }
    }

    protected function redirectToDashboard($role_name)
    {
        switch ($role_name) {
            case 'admin':
                return redirect('/admin/dashboard');
            case 'finance':
                return redirect('/finance/dashboard');
            case 'committee':
                return redirect('/committee/dashboard');
            case 'member':
                return redirect('/member/dashboard');
            default:
                return redirect('/welcome');
        }
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
            'terms' => 'required',
        ]);

        


       

        $response = Http::post($this->apiUrl . '/register', [
            'email' => $request->email,
            'password' => $request->password,
            'name' => $request->name,
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
            'Authorization' => 'Bearer ' . session('jwt_token'),
        ])->get($this->apiUrl . '/profile');
        
        if ($response->successful()) {
            $roleResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . session('jwt_token'),
            ])->get($this->apiUrl . '/user-role');
            
            $roleData = $roleResponse->json();
            
            return view('auth.profile', [
                'user' => $response->json(),
                'email' => session('user_email'),
                'role_id' => $roleData['role_id'],
                'role_name' => session('user_role_name'),
            ]);
        }
        
        return redirect('/login')->withErrors(['message' => 'Please login to view your profile']);
    }
    
    public function logout()
    {
        session()->forget(['jwt_token', 'user_email', 'user_role_id', 'user_role_name']);
        return redirect('/login');
    }
}
