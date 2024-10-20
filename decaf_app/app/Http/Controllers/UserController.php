<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use DB; 

class UserController extends Controller
{
    public function loginPost() {
        Log::info("UserController::loginPost triggered"); 
        $data = request()->post(); 

        if (isset($data["pass"]) && $data["pass"] == "c2hlIGJsaW5kZWQgbWUgd2l0aCBzY2llbmNl") {
            return view('index'); 
        } else if (isset($data["pass"]) && $data["pass"] == "1234") {
            return view('login')->with("message", ["Haha get rekt"]); 
        } else if (!isset($data["pass"])) {
            return view('login')->with("message", ["A blank password would be a pretty bad idea, but I'm glad you're trying everything"]); 
        }

        $pass = (isset($data["pass"])) ?  $data["pass"] . "?" : ""; 

        return view('login')->with("message", ["Nope", "Try again", "I'm afraid that is incorrect", "So close, yet so far", "Have you tried '1234'", "Hello I'm your cousin James and I'm stuck in Mexico, please send your credit card # so I can book a flight home", "If that's your best guess, we might be here a while", "...could you rephrase that?", "Say pretty please", "Nah I'm not feeling it right now, come back later", "What did you say? <strong>{$pass}</strong>"]); 
    }

    public function storyPost() {
        Log::info("UserController::storyPost triggered"); 
        $data = request()->post(); 

        // Login form will also be handled here (private and host)

        // TODO: CHECK if player posted but host left game 
        if (!isset($data["leave"]) && isset($data["new-text"]) && ((substr_count($data["new-text"], " ") + 1) > $data["turn-limit"])) {
            return view('story')->with("limitMessage", "Your message is too long! Write <strong>{$data["turn-limit"]} word(s)</strong> or less.");
        } else if (isset($data["new-text"]) && !isset($data["leave"]) && !isset($data["redo"])) {
            // Appending text to story
            $data["new-text"] = " " . $data["new-text"]; 

            DB::select("CALL updateStory(:newText, :gameId)", ["newText" => $data["new-text"], "gameId" => $data["game-id"]]); 

            Log::info("Text appended to story"); 
        } else if (isset($data["user"]) && isset($data["key"]) && isset($data["pass"])) {
            // Creating new story
            Log::info("Creating new story..."); 
            $gameId = DB::select("CALL createStory(:key, :pass, :user, :session, :title, :text, :limit, @gameId)", ["key" => $data["key"], "pass" => $data["pass"], "user" => $data["user"], "session" => $data["session"], "title" => $data["title"], "text" => $data["starter-text"], "limit" => $data["limit"]]);
            Log::info("New story created"); 

            $gameId = json_decode(json_encode($gameId, true), true);  

            return view('story')->with("gameId", $gameId); 
        } else if (isset($data["start-game"])) {
            // Starting game
            Log::info("Assigning player turns..."); 
            $sql = "SET @count := 0; "; 
            $sql .= "UPDATE PLAYER SET PLAY_TURN = @count := @count + 1 "; 
            $sql .= "WHERE GAME_ID = {$data["start-game"]} ORDER BY RAND();"; 
            DB::unprepared($sql); 

            $turns = DB::select("SELECT PLAY_USER, PLAY_SESSION, PLAY_TURN FROM PLAYER WHERE GAME_ID = ? ORDER BY PLAY_TURN ASC;", [$data["start-game"]]); 
            Log::info("Player turns assigned"); 

            Log::info("Starting game..."); 
            DB::update("UPDATE GAME SET GAME_RUN = 1 WHERE GAME_ID = ?", [$data["start-game"]]); 
            Log::info("Game started"); 

            $turns = json_decode(json_encode($turns, true), true);

            return view('story')->with('turns', $turns); 
        }

        return view('story');
    }

    // public function createStory() {
    //     Log::info("UserController::createStory triggered"); 
    //     $data = request()->post(); 

        

    //     return view('story');
    // }
}
