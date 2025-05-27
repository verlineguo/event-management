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

        if ($response->successful()) {
            $events = $response->json();
            return view('committee.event.index', compact('events'));
        }

        return back()->withErrors(['message' => 'Gagal mengambil data events']);
    }

    public function create()
    {
        return view('committee.event.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date|after_or_equal:today',
            'time' => 'required',
            'location' => 'required|string|max:255',
            'speaker' => 'required|string|max:255',
            'poster' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'registration_fee' => 'required|numeric|min:0',
            'max_participants' => 'required|integer|min:1',
            'status' => 'required|in:open,closed,cancelled,completed',
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
                'date' => $request->date,
                'time' => $request->time,
                'location' => $request->location,
                'speaker' => $request->speaker,
                'poster' => $posterPath, 
                'registration_fee' => $request->registration_fee,
                'max_participants' => $request->max_participants,
                'created_by' => session('user_id'),
                'status' => $request->status,
            ];

            $response = Http::withToken(session('jwt_token'))->post($this->apiUrl . '/events', $eventData);
            
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

        if ($eventResponse->successful()) {
            $event = $eventResponse->json();
            return view('committee.event.edit', compact('event'));
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
            'date' => 'required|date',
            'time' => 'required',
            'location' => 'required|string|max:255',
            'speaker' => 'required|string|max:255',
            'poster' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'registration_fee' => 'required|numeric|min:0',
            'max_participants' => 'required|integer|min:1',
            'status' => 'required|in:open,closed,cancelled,completed',
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
                'date' => $request->date,
                'time' => $request->time,
                'location' => $request->location,
                'speaker' => $request->speaker,
                'poster' => $posterPath, // Cuma path aja
                'registration_fee' => $request->registration_fee,
                'max_participants' => $request->max_participants,
                'status' => $request->status,
            ];

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
}