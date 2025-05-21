<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GuestMainController extends Controller
{
    public function index() {
        return view('guest.home');
    }
}
