<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller {
    public function login(Request $request) {
        Log::info("Running LoginController"); 
        // $login = $request->session()->get("LOGIN_SUCCESS"); 
        $msg = $request->msg; 
       
        return view('login')->with("msg", $msg); 
    }
}
