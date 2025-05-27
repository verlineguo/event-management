<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CommitteeMainController extends Controller
{
    public function index() {
        return view('committee.dashboard');
    }
}
