<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CategoryController extends Controller
{
    private $apiUrl = 'http://localhost:5000/api';

    public function index()
    {
        $response = Http::withToken(session('jwt_token'))->get($this->apiUrl . '/category');

        if ($response->successful()) {
            $categories = $response->json();
            return view('admin.category.index', compact('categories'));
        }

        return back()->withErrors(['message' => 'Gagal mengambil data categorys']);
    }

    public function create()
    {
        return view('admin.category.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|boolean',

        ]);

        try {
            $categoryData = [
                'name' => $request->name,
                'status' => $request->status,

            ];

            $response = Http::withToken(session('jwt_token'))->post($this->apiUrl . '/category', $categoryData);
            
            if ($response->successful()) {
                return redirect()->route('admin.category.index')->with('success', 'category berhasil dibuat');
            }

            $error = $response->json('message') ?? 'Gagal membuat category';
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
        $categoryResponse = Http::withToken(session('jwt_token'))->get($this->apiUrl . "/category/{$id}");

        if ($categoryResponse->successful()) {
            $category = $categoryResponse->json();

            return view('admin.category.edit', compact('category'));
        }

        return back()->withErrors(['message' => 'Gagal mengambil data category']);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|boolean',
        ]);

        try {
            $categoryResponse = Http::withToken(session('jwt_token'))->get($this->apiUrl . "/category/{$id}");

            if (!$categoryResponse->successful()) {
                return back()->withErrors(['message' => 'Gagal mengambil data category yang akan diupdate']);
            }


            $categoryData = [
                'name' => $request->name,
                'status' => $request->status
            ];

            $response = Http::withToken(session('jwt_token'))->put($this->apiUrl . "/category/{$id}", $categoryData);

            if ($response->successful()) {
                return redirect()->route('admin.category.index')->with('success', 'Category berhasil diupdate');
            }

            $errorMessage = $response->json('message') ?? 'Gagal update category';
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
        $response = Http::withToken(session('jwt_token'))->delete($this->apiUrl . "/category/{$id}");

        if ($response->successful()) {
            return redirect()->route('admin.category.index')->with('success', 'Category berhasil dihapus');
        }

        return back()->withErrors(['message' => 'Gagal menghapus category']);
    }
}
