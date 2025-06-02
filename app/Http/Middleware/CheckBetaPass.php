<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class CheckBetaPass {
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response {
        $pass = $request->input("login-pass"); 
        $login = $request->session()->get("LOGIN_SUCCESS"); 
        $msg = []; 

        Log::info("CheckBetaPass login --> {$login}"); 
        if (isset($login)) return redirect()->route('index'); 

        if (isset($pass)) {
            switch($pass) {
                case "c2hlIGJsaW5kZWQgbWUgd2l0aCBzY2llbmNl":
                    Log::info("Login success CheckBetaPass"); 
                    session(["LOGIN_SUCCESS" => true]); 

                    return redirect()->route('index');
                case "1234": 
                    $msg = ["Haha get rekt"]; 
                    break; 
                default: 
                    $msg = ["No", "Try again", "That is incorrect", "So close, yet so far", "Have you tried '1234'", "Hello I'm your cousin James and I'm stuck in Mexico, please send your credit card # so I can book a flight home", "Wrong", "Could you rephrase that?", "Say pretty please", "Try again later", "What did you say? <strong>{$pass}?</strong>"]; 
            }
        }

        $request->merge(compact('msg')); 
        return $next($request);
    }
}
