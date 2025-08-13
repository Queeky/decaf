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
