<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GuestMainController extends Controller
{

    private $apiUrl = 'http://localhost:5000/api';

    public function index(Request $request)
    {
        $query = Event::query();

        $response = Http::withToken(session('jwt_token'))->get($this->apiUrl . '/events');

        if ($response->successful()) {
            $events = $response->json();
            return view('guest.home', compact('events'));
        }
        return back()->withErrors(['message' => 'Gagal mengambil data events']);

            if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where('name', 'like', '%' . $search . '%')
                // ->orWhere('location', 'like', '%' . $search . '%')
                // ->orWhere('speaker', 'like', '%' . $search . '%')
            ;}

        $events = $query->orderBy('date', 'asc')->get();

        return view('guest.events.index', compact('events'));
    }


    public function about()
    {
        $response = Http::withToken(session('jwt_token'))->get($this->apiUrl . '/events');

        if ($response->successful()) {
            $events = $response->json();
            return view('guest.events', compact('events'));
        }

        return back()->withErrors(['message' => 'Gagal mengambil data events']);
    }
}
