<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    private $apiUrl = 'http://localhost:5000/api';

    public function index()
    {
        $response = Http::withToken(session('jwt_token'))->get($this->apiUrl . '/events');
        $categoriesResponse = Http::withToken(session('jwt_token'))->get($this->apiUrl . '/category');
        $categories = $categoriesResponse->successful() ? $categoriesResponse->json() : [];
        if ($response->successful()) {
            $events = $response->json();
            Log::info($events);
            return view('committee.event.index', compact('events', 'categories'));
        }

        return back()->withErrors(['message' => 'Gagal mengambil data events']);
    }

    public function create()
    {
        // Get categories for dropdown
        $categoriesResponse = Http::withToken(session('jwt_token'))->get($this->apiUrl . '/category');
        $categories = $categoriesResponse->successful() ? $categoriesResponse->json() : [];

        return view('committee.event.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|string',
            'poster' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'max_participants' => 'required|integer|min:1',
            'status' => 'required|in:open,closed,cancelled,completed',
            'sessions' => 'nullable|array',
            'sessions.*.title' => 'required_with:sessions|string|max:255',
            'sessions.*.description' => 'nullable|string',
            'sessions.*.date' => 'required_with:sessions|date',
            'sessions.*.session_fee' => 'required|numeric|min:0',
            'sessions.*.start_time' => 'required_with:sessions|string',
            'sessions.*.end_time' => 'required_with:sessions|string',
            'sessions.*.location' => 'required_with:sessions|string|max:255',
            'sessions.*.speaker' => 'required_with:sessions|string|max:255',
            'sessions.*.max_participants' => 'nullable|integer|min:1',
        ]);

        try {
            $posterPath = null;

            // Handle poster upload jika ada
            if ($request->hasFile('poster')) {
                $poster = $request->file('poster');
                $posterName = time() . '_' . $poster->getClientOriginalName();

                // Simpan ke storage/app/public/posters dan return path relatif
                $posterPath = $poster->storeAs('posters', $posterName, 'public');
            }

            $eventData = [
                'name' => $request->name,
                'description' => $request->description,
                'category_id' => $request->category_id,
                'poster' => $posterPath,
                'max_participants' => $request->max_participants,
                'created_by' => session('user_id'),
                'status' => $request->status,
            ];

            // Add sessions if provided
            if ($request->has('sessions') && is_array($request->sessions)) {
                $eventData['sessions'] = $request->sessions;
            }

            $response = Http::withToken(session(key: 'jwt_token'))->post($this->apiUrl . '/events', $eventData);

            if ($response->successful()) {
                return redirect()->route('committee.event.index')->with('success', 'Event berhasil dibuat');
            }

            $error = $response->json('message') ?? 'Gagal membuat event';
            return back()
                ->withErrors(['message' => $error])
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Exception when creating event: ' . $e->getMessage());

            return back()
                ->withErrors(['message' => 'Terjadi kesalahan: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function edit($id)
    {
        $eventResponse = Http::withToken(session('jwt_token'))->get($this->apiUrl . "/events/{$id}");
        $categoriesResponse = Http::withToken(session('jwt_token'))->get($this->apiUrl . '/category');

        if ($eventResponse->successful()) {
            $event = $eventResponse->json();
            $categories = $categoriesResponse->successful() ? $categoriesResponse->json() : [];
            $sessions = $event['sessions'] ?? [];

            return view('committee.event.edit', compact('event', 'categories', 'sessions'));
        }

        return back()->withErrors(['message' => 'Gagal mengambil data event']);
    }

    public function show($id)
    {
        $eventResponse = Http::withToken(session('jwt_token'))->get($this->apiUrl . "/events/{$id}");

        if ($eventResponse->successful()) {
            $event = $eventResponse->json();
            return view('committee.event.show', compact('event'));
        }

        return back()->withErrors(['message' => 'Gagal mengambil data event']);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|string',
            'poster' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'max_participants' => 'required|integer|min:1',
            'status' => 'required|in:open,closed,cancelled,completed',
            'sessions' => 'nullable|array',
            'sessions.*.title' => 'required_with:sessions|string|max:255',
            'sessions.*.description' => 'nullable|string',
            'sessions.*.date' => 'required_with:sessions|date',
            'sessions.*.start_time' => 'required_with:sessions|string',
            'sessions.*.session_fee' => 'required|numeric|min:0',
            'sessions.*.end_time' => 'required_with:sessions|string',
            'sessions.*.location' => 'required_with:sessions|string|max:255',
            'sessions.*.speaker' => 'required_with:sessions|string|max:255',
            'sessions.*.max_participants' => 'nullable|integer|min:1',
            'sessions.*.status' => 'nullable|in:scheduled,ongoing,completed,cancelled',
        ]);

        try {
            $eventResponse = Http::withToken(session('jwt_token'))->get($this->apiUrl . "/events/{$id}");

            if (!$eventResponse->successful()) {
                return back()->withErrors(['message' => 'Gagal mengambil data event yang akan diupdate']);
            }

            $existingEvent = $eventResponse->json();
            $posterPath = $existingEvent['poster'] ?? null;

            // Handle poster upload jika ada file baru
            if ($request->hasFile('poster')) {
                // Hapus poster lama jika ada
                if ($posterPath) {
                    Storage::disk('public')->delete($posterPath);
                }

                $poster = $request->file('poster');
                $posterName = time() . '_' . $poster->getClientOriginalName();
                $posterPath = $poster->storeAs('posters', $posterName, 'public');
            }

            $eventData = [
                'name' => $request->name,
                'description' => $request->description,
                'category_id' => $request->category_id,
                'poster' => $posterPath,
                'max_participants' => $request->max_participants,
                'status' => $request->status,
            ];

            // Add sessions if provided
            if ($request->has('sessions') && is_array($request->sessions)) {
                $eventData['sessions'] = $request->sessions;
            }

            $response = Http::withToken(session('jwt_token'))->put($this->apiUrl . "/events/{$id}", $eventData);

            if ($response->successful()) {
                return redirect()->route('committee.event.index')->with('success', 'Event berhasil diupdate');
            }

            $errorMessage = $response->json('message') ?? 'Gagal update event';
            return back()
                ->withErrors(['message' => $errorMessage])
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Exception when updating event: ' . $e->getMessage());

            return back()
                ->withErrors(['message' => 'Terjadi kesalahan: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            // Ambil data event untuk mendapatkan poster path
            $eventResponse = Http::withToken(session('jwt_token'))->get($this->apiUrl . "/events/{$id}");

            if ($eventResponse->successful()) {
                $event = $eventResponse->json();

                // Hapus poster jika ada
                if (isset($event['poster']) && $event['poster']) {
                    Storage::disk('public')->delete($event['poster']);
                }
            }

            $response = Http::withToken(session('jwt_token'))->delete($this->apiUrl . "/events/{$id}");

            if ($response->successful()) {
                return redirect()->route('committee.event.index')->with('success', 'Event berhasil dihapus');
            }

            return back()->withErrors(['message' => 'Gagal menghapus event']);
        } catch (\Exception $e) {
            Log::error('Exception when deleting event: ' . $e->getMessage());

            return back()->withErrors(['message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }


    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:open,closed,cancelled,completed',
        ]);

        try {
            $response = Http::withToken(session('jwt_token'))->patch($this->apiUrl . "/events/{$id}/status", [
                'status' => $request->status,
            ]);

            if ($response->successful()) {
                return redirect()->route('committee.event.index')->with('success', 'Status event berhasil diupdate');
            }

            return redirect()->back()->with('error', 'Gagal mengupdate status event');
        } catch (\Exception $e) {
            Log::error('Exception when updating event status: ' . $e->getMessage());

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function search(Request $request)
    {
        $params = [];

        if ($request->filled('q')) {
            $params['q'] = $request->q;
        }

        if ($request->filled('category')) {
            $params['category'] = $request->category;
        }

        if ($request->filled('status')) {
            $params['status'] = $request->status;
        }

        $response = Http::withToken(session('jwt_token'))
            ->timeout(30)
            ->get($this->apiUrl . '/events', $params);

        Log::info('API Response Status:', ['status' => $response->status()]);

        if ($response->successful()) {
            $events = $response->json();

            $categoriesResponse = Http::withToken(session('jwt_token'))->get($this->apiUrl . '/category');

            $categories = $categoriesResponse->successful() ? $categoriesResponse->json() : [];

            if ($request->ajax()) {
                return response()->json($events);
            }

            return view('committee.event.index', compact('events', 'categories'));
        }

        Log::error('Search API Error:', ['response' => $response->body()]);
        return back()->withErrors(['message' => 'Gagal melakukan pencarian events: ' . $response->body()]);
    }
}
