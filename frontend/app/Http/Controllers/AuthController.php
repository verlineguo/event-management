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

        try {
            $response = Http::post($this->apiUrl . '/login', [
                'email' => $request->email,
                'password' => $request->password,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                // Pastikan token ada sebelum menyimpan ke session
                if (isset($data['token']) && !empty($data['token'])) {
                    session([
                        'jwt_token' => $data['token'],
                        'user_email' => $data['user']['email'],
                        'user_role' => $data['user']['role_id'], // Tambahkan fallback jika role_id tidak ada
                    ]);

                    Log::info('JWT token stored successfully:', ['token_length' => strlen($data['token'])]);

                    // Periksa apakah role_id valid untuk redirect
                    if (isset($data['user']['role_id'])) {
                        return $this->redirectToDashboard($data['user']['role_id']);
                    } else {
                        Log::error('Role ID missing in user data');
                        return redirect('/welcome');
                    }
                } else {
                    Log::error('Token is missing in API response', ['response' => $data]);
                    return back()->withErrors(['message' => 'Authentication token missing']);
                }
            } else {
                Log::error('Login API failed', ['status' => $response->status(), 'response' => $response->body()]);
                return back()->withErrors(['message' => 'Login failed: ' . ($response->json()['error'] ?? 'Unknown error')]);
            }
        } catch (\Exception $e) {
            Log::error('Exception during login', ['message' => $e->getMessage()]);
            return back()->withErrors(['message' => 'Login service unavailable']);
        }
    }

    protected function redirectToDashboard($role_id)
    {
        switch ($role_id) {
            case 1:
                return redirect('/admin/dashboard');
            case 2:
                return redirect('/finance/dashboard');
            case 3:
                return redirect('/committee/dashboard');
            case 4:
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
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
        ]);

        $response = Http::post($this->apiUrl . '/register', [
            'email' => $request->email,
            'password' => $request->password,
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
