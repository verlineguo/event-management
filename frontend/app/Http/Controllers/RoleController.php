<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RoleController extends Controller
{
    private $apiUrl = 'http://localhost:5000/api';

    public function index()
    {
        $response = Http::withToken(session('jwt_token'))->get($this->apiUrl . '/role');
        if ($response->successful()) {
            $roles = $response->json();
            return view('admin.role.index', compact('roles'));
        }

        return back()->withErrors(['message' => 'Gagal mengambil data roles']);
    }

    public function create()
    {
        return view('admin.role.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|boolean',

        ]);

        try {
            $roleData = [
                'name' => $request->name,
                'status' => $request->status,

            ];

            $response = Http::withToken(session('jwt_token'))->post($this->apiUrl . '/role', $roleData);

            if ($response->successful()) {
                return redirect()->route('admin.role.index')->with('success', 'Role berhasil dibuat');
            }

            $error = $response->json('message') ?? 'Gagal membuat role';
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
        $roleResponse = Http::withToken(session('jwt_token'))->get($this->apiUrl . "/role/{$id}");

        if ($roleResponse->successful()) {
            $role = $roleResponse->json();

            return view('admin.role.edit', compact('role'));
        }

        return back()->withErrors(['message' => 'Gagal mengambil data role']);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|boolean',
        ]);

        try {
            $roleResponse = Http::withToken(session('jwt_token'))->get($this->apiUrl . "/role/{$id}");

            if (!$roleResponse->successful()) {
                return back()->withErrors(['message' => 'Gagal mengambil data role yang akan diupdate']);
            }


            $roleData = [
                'name' => $request->name,
                'status' => $request->status
            ];

            $response = Http::withToken(session('jwt_token'))->put($this->apiUrl . "/role/{$id}", $roleData);

            if ($response->successful()) {
                return redirect()->route('admin.role.index')->with('success', 'Role berhasil diupdate');
            }

            $errorMessage = $response->json('message') ?? 'Gagal update role';
            return back()
                ->withErrors(['message' => $errorMessage])
                ->withInput();
        } catch (\Exception $e) {

            return back()
                ->withErrors(['message' => 'Terjadi kesalahan: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function destroy($id)
    {
        $response = Http::withToken(session('jwt_token'))->delete($this->apiUrl . "/role/{$id}");

        if ($response->successful()) {
            return redirect()->route('admin.role.index')->with('success', 'Role berhasil dihapus');
        }

        return back()->withErrors(['message' => 'Gagal menghapus role']);
    }
}
