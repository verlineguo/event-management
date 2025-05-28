<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MemberMainController extends Controller
{
    private $apiUrl = 'http://localhost:5000/api';

    public function index()
    {
        $response = Http::withToken(session('jwt_token'))->get($this->apiUrl . '/events');

        if ($response->successful()) {
            $events = $response->json();
            return view('member.home', compact('events'));
        }

        return back()->withErrors(['message' => 'Gagal mengambil data events']);
    }
}
