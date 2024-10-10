<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use DB; 

class UserController extends Controller
{
    public function storyPost() {
        Log::info("UserController::storyPost triggered"); 
        $data = request()->post(); 

        // CHECK if player posted but host left game
        if ((substr_count($data["new-text"], " ") + 1) > $data["turn-limit"]) {
            return view('story')->with("limitMessage", "Your message is too long! Write <strong>{$data["turn-limit"]} word(s)</strong> or less.");
        } else if (isset($data["new-text"]) && !isset($data["leave"]) && !isset($data["redo"])) {
            // Appending text to story
            $data["new-text"] = " " . $data["new-text"]; 

            DB::select("CALL updateStory(:newText, :gameId)", ["newText" => $data["new-text"], "gameId" => $data["game-id"]]); 

            Log::info("Text appended to story"); 
        } else if (isset($data["user"]) && isset($data["key"]) && isset($data["pass"])) {
            // Creating new story
            $gameId = DB::select("CALL createStory(:key, :pass, :user, :session, :title, :text, :limit, @gameId)", ["key" => $data["key"], "pass" => $data["pass"], "user" => $data["user"], "session" => $data["session"], "title" => $data["title"], "text" => $data["starter-text"], "limit" => $data["limit"]]);

            Log::info("New story created"); 

            $gameId = json_decode(json_encode($gameId, true), true);  

            return view('story')->with("gameId", $gameId); 
        }

        return view('story');
    }

    // public function createStory() {
    //     Log::info("UserController::createStory triggered"); 
    //     $data = request()->post(); 

        

    //     return view('story');
    // }
}
