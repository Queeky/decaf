<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use DB; 

class UserController extends Controller
{
    public function appendStory() {
        Log::info("POST form triggered UserController::appendStory"); 
        $data = request()->post(); 

        if (!isset($data["leave"]) && !isset($data["redo"])) {
            $data["new-text"] = " " . $data["new-text"] . " "; 

            DB::select("CALL updateStory(:newText, :gameId)", ["newText" => $data["new-text"], "gameId" => $data["game-id"]]); 
        }

        return view('story');
    }
}
