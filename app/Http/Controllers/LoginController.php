<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller {
    public function login(Request $request) {
        $msg = $request->msg; 
        return view('login')->with("msg", $msg); 
    }
}
