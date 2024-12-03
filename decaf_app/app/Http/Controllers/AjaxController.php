<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use DB; 

class AjaxController extends Controller
{
    public function testPost() {
        Log::info("AjaxController::testPost triggered"); 

        // $data = request()->post(); 
        // if (isset($data["fakeInput"])) {
        //     Log::info("Is this working? -> " . $data["fakeInput"]); 
        // }

        $text = "ID #" . rand(0, 2000); 

        Log::info("Execution " . $text); 

        $x = DB::select("SELECT * FROM TEST LIMIT 1"); 
        $x = json_decode(json_encode($x, true), true)[0]; 

        return response()->json([
            'html' => view('test', compact('x'))->render()
        ]);
    }
}
