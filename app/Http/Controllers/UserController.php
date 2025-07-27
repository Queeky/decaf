<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use DB; 

class UserController extends Controller
{
    public function storyPost() {
        $data = request()->post(); 

        // 3. User leaves game (host or player)
        if (isset($data["leave"]["user"])) {
            // Collecting finished story
            $storyComplete = DB::select("SELECT STORY_ID, STORY_TITLE, STORY_TEXT FROM STORY WHERE GAME_ID = ?", [$data["leave"]["id"]]); 
            $storyComplete = json_decode(json_encode($storyComplete, true), true)[0];

            if (isset($data["leave"]["host"])) {
                // Host left, remove game
                DB::select("CALL endGame(?)", [$data["leave"]["id"]]); 

                Log::info("Story finished! --> GAME #" . $data["leave"]["id"]); 
            } else {
                // Player left, remove player from game
                DB::delete("DELETE FROM PLAYER WHERE PLAY_USER = ? AND GAME_ID = ?", [$data["leave"]["user"], $data["leave"]["id"]]); 
            }

            Log::info("GAME #" . $data["leave"]["id"] . ": " . $data["leave"]["user"] . " left"); 

            return view('story')->with(compact("storyComplete")); 
        } 
        
        
        // 7. Host creates a new story
        if (isset($data["host-user"]) || isset($data["host-key"]) || isset($data["host-pass"]) || isset($data["make-public"]) || isset($data["host-title"]) || isset($data["host-limit"]) || isset($data["starter-text"])) {
            if (isset($data["host-user"]) && isset($data["make-public"]) && isset($data["host-title"]) && isset($data["host-limit"]) && isset($data["starter-text"])) {
                // Check if key is valid
                if (array_intersect(str_split("!@#$%^&*()-_+={}[]|\\/<>,.;:\"'~`"), str_split($data["host-key"]))) {
                    $err = ["errCode" => "JH", "errMsg" => "Your room key cannot include special characters."]; 
                } 

                if ($data["make-public"] == "n" && !isset($data["host-key"])) {
                    $err = ["errCode" => "JH", "errMsg" => "Private games must have a room key."]; 
                }

                if ($data["make-public"] == "n" && !isset($data["host-pass"])) {
                    $err = ["errCode" => "JH", "errMsg" => "Private games must have a password."]; 
                }

                if ($data["host-limit"] < 1) {
                    $err = ["errCode" => "JH", "errMsg" => "Your word limit cannot be less than 1."]; 
                }

                // If private, uppercase submitted key; if public, key becomes RANDOM
                $data["host-key"] = ($data["make-public"] == "n") ? strtoupper($data["host-key"]) : "RANDOM";

                // Check if key is already in use
                // Eventually incorporate this in main select below
                if ($data["make-public"] == "n") {
                    $exists = DB::select("SELECT GAME_ID FROM GAME WHERE GAME_KEY = ?", [$data["host-key"]]); 

                    if ($exists) {
                        $err = ["errCode" => "JH", "errMsg" => "This key already exists."];
                    } 
                }

                // Checks if any errors were set above
                if (isset($err)) {
                    return view('story')->with("err", $err); 
                }
    
                Log::info("Creating new story...");  

                $data["host-pass"] = ($data["make-public"] == "n") ? Hash::make($data["host-pass"]) : null; 
    
                $gameId = DB::select("CALL createStory(:key, :pass, :user, :session, :title, :text, :limit, @gameId, @storyId)", ["key" => $data["host-key"], "pass" => $data["host-pass"], "user" => $data["host-user"], "session" => $data["session"], "title" => $data["host-title"], "text" => $data["starter-text"], "limit" => $data["host-limit"]]);
                $gameId = json_decode(json_encode($gameId, true), true);  
    
                return view('story')->with("gameId", $gameId); 
            } else {
                $err = ["errCode" => "JH", "errMsg" => "You must fill out all necessary fields."]; 
    
                return view('story')->with("err", $err); 
            }
        }
        
        // 8. Host starts game
        if (isset($data["start-game"])) {
            // Starting game
            Log::info("GAME #" . $data["start-game"] . ": Assigning player turns"); 
            $sql = "SET @count := 0; "; 
            $sql .= "UPDATE PLAYER SET PLAY_TURN = @count := @count + 1 "; 
            $sql .= "WHERE GAME_ID = {$data["start-game"]} ORDER BY RAND();"; 
            DB::unprepared($sql); 

            $turns = DB::select("SELECT PLAY_USER, PLAY_SESSION, PLAY_TURN FROM PLAYER WHERE GAME_ID = ? ORDER BY PLAY_TURN ASC;", [$data["start-game"]]); 

            Log::info("GAME #" . $data["start-game"] . ": Starting game"); 
            DB::update("UPDATE GAME SET GAME_RUN = 1 WHERE GAME_ID = ?", [$data["start-game"]]); 

            $turns = json_decode(json_encode($turns, true), true);

            return view('story')->with('turns', $turns); 
        } 

        // 9. Player leaves story result screen
        if (isset($data["leave-story-result"])) {
            return view('story')->with('unset2', true);
        }

        // 10. Host or admin deletes story
        if (isset($data["delete-story"]) || isset($data["admin-delete"])) {
            $id = isset($data["delete-story"]) ? [$data["delete-story"], "host"] : [$data["admin-delete"], "admin"]; 

            DB::delete("DELETE FROM STORY WHERE STORY_ID = ?", [$id[0]]); 

            Log::info("STORY #" . $id[0] . " was removed by " . $id[1]); 

            return view('story')->with('unset2', true); 
        }

        // 11. Host publishes story
        if (isset($data["publish-story"])) {
            DB::update("UPDATE STORY SET STORY_PUBLISH = 1 WHERE STORY_ID = ?", [$data["publish-story"]]); 

            Log::info("STORY #" . $data["publish-story"] . " was published"); 

            return view('story')->with('unset2', true); 
        }

        return view('story'); 
    }
}
