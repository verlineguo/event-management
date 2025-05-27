<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MemberMainController extends Controller
{
    public function index() {
        return view('member.home');
    }
}
