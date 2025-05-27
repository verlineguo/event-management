<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FinanceMainController extends Controller
{
    public function index() {
        return view('finance.dashboard');
    }
}
