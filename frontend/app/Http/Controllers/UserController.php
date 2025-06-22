<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    private $apiUrl = 'http://localhost:5000/api';

    public function index()
    {
        $response = Http::withToken(session('jwt_token'))->get($this->apiUrl . '/users');

        if ($response->successful()) {
            $users = $response->json();
            return view('admin.user.index', compact('users'));
        }

        return back()->withErrors(['message' => 'Gagal mengambil data users']);
    }

    public function create()
    {
        $response = Http::withToken(session('jwt_token'))->get($this->apiUrl . '/role');
        if ($response->successful()) {
            $roles = $response->json();
            
            return view('admin.user.create', compact('roles'));
        }
        return back()->withErrors(['message' => 'Gagal mengambil data roles']);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:6',
            'gender' => 'required|in:male,female',
            'phone' => 'nullable|string|max:20',
            'role_id' => 'required|string',
            'status' => 'required|boolean',

        ]);

        try {
            $userData = [
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password,
                'gender' => $request->gender,
                'role_id' => $request->role_id,
                'phone' => $request->phone,
                'status' => $request->status,

            ];

            $response = Http::withToken(session('jwt_token'))->post($this->apiUrl . '/users', $userData);
            if ($response->successful()) {
                return redirect()->route('admin.user.index')->with('success', 'User berhasil dibuat');
            }

            $error = $response->json('message') ?? 'Gagal membuat user';
            return back()
                ->withErrors(['message' => $error])
                ->withInput();
        } catch (\Exception $e) {
            return back()
                ->withErrors(['message' => 'Terjadi kesalahan: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function edit($id)
    {
        $rolesResponse = Http::withToken(session('jwt_token'))->get($this->apiUrl . '/role');
        $userResponse = Http::withToken(session('jwt_token'))->get($this->apiUrl . "/users/{$id}");

        if ($rolesResponse->successful() && $userResponse->successful()) {
            $roles = $rolesResponse->json();
            $user = $userResponse->json();

            return view('admin.user.edit', compact('user', 'roles'));
        }

        return back()->withErrors(['message' => 'Gagal mengambil data user atau roles']);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'gender' => 'nullable|in:male,female',
            'phone' => 'nullable|string|max:20',
            'new_password' => 'nullable|string|min:6',
            'confirm_password' => 'nullable|same:new_password',
            'status' => 'required|boolean',
        ]);

        try {
            $userResponse = Http::withToken(session('jwt_token'))->get($this->apiUrl . "/users/{$id}");

            if (!$userResponse->successful()) {
                return back()->withErrors(['message' => 'Gagal mengambil data user yang akan diupdate']);
            }

            $existingUser = $userResponse->json();

            $userData = [
                'name' => $request->name,
                'email' => $request->email,
                'role_id' => $request->role_id,
                'gender' => $request->gender,
                'phone' => $request->phone,
                'password' => $request->filled('new_password') ? $request->new_password : $existingUser['password'] ?? '',
                'status' => $request->status
            ];

            $response = Http::withToken(session('jwt_token'))->put($this->apiUrl . "/users/{$id}", $userData);

            if ($response->successful()) {
                return redirect()->route('admin.user.index')->with('success', 'User berhasil diupdate');
            }

            $errorMessage = $response->json('message') ?? 'Gagal update user';
            return back()
                ->withErrors(['message' => $errorMessage])
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Exception when updating user: ' . $e->getMessage());

            return back()
                ->withErrors(['message' => 'Terjadi kesalahan: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function destroy($id)
    {
        $response = Http::withToken(session('jwt_token'))->delete($this->apiUrl . "/users/{$id}");

        if ($response->successful()) {
            return redirect()->route('admin.user.index')->with('success', 'User berhasil dihapus');
        }

        return back()->withErrors(['message' => 'Gagal menghapus user']);
    }
}
