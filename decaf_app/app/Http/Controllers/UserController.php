<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use DB; 

class UserController extends Controller
{
    public function loginPost() {
        $data = request()->post(); 

        if (isset($data["pass"]) && $data["pass"] == "c2hlIGJsaW5kZWQgbWUgd2l0aCBzY2llbmNl") {
            Log::info("Beta player login success"); 
            return view('login')->with("loginTrue", [true]); 
        } else if (isset($data["pass"]) && $data["pass"] == "1234") {
            Log::info("Beta player login failure"); 
            return view('login')->with("message", ["Haha get rekt"]); 
        } else if (!isset($data["pass"])) {
            Log::info("Beta player login failure"); 
            return view('login')->with("message", ["A blank password would be a pretty bad idea, but I'm glad you're trying everything"]); 
        }

        $pass = (isset($data["pass"])) ?  $data["pass"] . "?" : ""; 

        Log::info("Beta player login failure"); 

        return view('login')->with("message", ["Nope", "Try again", "I'm afraid that is incorrect", "So close, yet so far", "Have you tried '1234'", "Hello I'm your cousin James and I'm stuck in Mexico, please send your credit card # so I can book a flight home", "If that's your best guess, we might be here a while", "...could you rephrase that?", "Say pretty please", "Nah I'm not feeling it right now, come back later", "What did you say? <strong>{$pass}</strong>"]); 
    }

    public function storyGet() {
        $data = request()->input(); 

        // Log::info("DEBUG is this triggering more than once?"); 

        // Private game login
        if (isset($data["key"]) || isset($data["pass"]) || isset($data["user"])) {
            if (isset($data["key"]) && isset($data["pass"]) && isset($data["user"])) {
                $data["key"] = strtoupper($data["key"]); 

                $avail = DB::select("SELECT GAME.GAME_ID, GAME.GAME_KEY, GAME.GAME_PASS, GAME.GAME_RUN, GAME.GAME_TURN, STORY.STORY_TITLE, STORY.STORY_TEXT, STORY.STORY_TURN_LIMIT FROM GAME JOIN STORY ON GAME.GAME_ID = STORY.GAME_ID WHERE GAME_KEY = ? AND GAME_PASS = ?", [$data["key"], $data["pass"]]); 

                $avail = json_decode(json_encode($avail, true), true);

                if ($avail) {
                    return view('story')->with("avail", $avail[0]);
                } else {
                    $err = ["errCode" => "JP", "errMsg" => "This game does not exist."]; 

                    return view('story')->with("err", $err);
                }
            } else {
                $err = ["errCode" => "JP", "errMsg" => "You must fill out all fields."]; 

                return view('story')->with("err", $err); 
            }
        }

        return view('story'); 
    }

    public function storyPost() {
        $data = request()->post(); 

        // Private game login
        if (isset($data["join-key"]) || isset($data["join-pass"]) || isset($data["join-user"])) {
            if (isset($data["join-key"]) && isset($data["join-pass"]) && isset($data["join-user"])) {
                $data["join-key"] = strtoupper($data["join-key"]); 

                $avail = DB::select("SELECT GAME.GAME_ID, GAME.GAME_KEY, GAME.GAME_PASS, GAME.GAME_RUN, GAME.GAME_TURN, STORY.STORY_TITLE, STORY.STORY_TEXT, STORY.STORY_TURN_LIMIT FROM GAME JOIN STORY ON GAME.GAME_ID = STORY.GAME_ID WHERE GAME_KEY = ? AND GAME_PASS = ?", [$data["join-key"], $data["join-pass"]]); 

                $avail = json_decode(json_encode($avail, true), true);
                $joinUser = $data["join-user"]; 

                if ($avail) {
                    // return view('story')->with("avail", $avail[0]);
                    return view('story')->with(compact("avail", "joinUser"));
                } else {
                    $err = ["errCode" => "JP", "errMsg" => "This game does not exist."]; 

                    return view('story')->with("err", $err);
                }
            } else {
                $err = ["errCode" => "JP", "errMsg" => "You must fill out all fields."]; 

                return view('story')->with("err", $err); 
            }
        }




        // When using long if statements like this, need to write 
        // priority list and eventually reorganize
        // TODO: CHECK if player posted but host left game 

        // Log::info("WAIT TURN --> "); 
        // if (isset($data)) Log::info(var_dump($data)); 
        if (empty($data)) Log::info("This bitch is empty"); 

        if (isset($data["redo"])) {
            return view('story');
        } else if (isset($data["leave"]["user"])) {
            if (isset($data["leave"]["host"])) {
                // Host left, remove game
                DB::select("CALL endGame(?)", [$data["leave"]["id"]]); 

                Log::info("GAME #" . $data["leave"]["id"] . " ended!"); 
            } else {
                // Player left, remove player from game
                DB::delete("DELETE FROM PLAYER WHERE PLAY_USER = ? AND GAME_ID = ?", [$data["leave"]["user"], $data["leave"]["id"]]); 
            }

            Log::info("GAME #" . $data["leave"]["id"] . ": " . $data["leave"]["user"] . " left"); 

            return view('story')->with("leftGame", true); 
        } else if (isset($data["wait-turn"])) {
            Log::info("GAME #" . $data["wait-turn"] . ": " . $data["wait-player"] . " is waiting their turn"); 

            $gameTurn = DB::select("SELECT GAME_TURN FROM GAME WHERE GAME_ID = ?", [$data["wait-turn"]]); 
            $gameTurn = json_decode(json_encode($gameTurn, true), true)[0];

            return response()->json([
                'html' => view('story', compact('gameTurn'))->render()
            ]);
        } else if (!isset($data["leave"]["user"]) && isset($data["new-text"]) && ((substr_count($data["new-text"], " ") + 1) > $data["turn-limit"])) {
            Log::info("GAME #" . $data["game-id"] . ": Message is too short"); 

            return view('story')->with("limitMessage", "Your message is too long! Write <strong>{$data["turn-limit"]} word(s)</strong> or less.");
        } else if (isset($data["new-text"]) && !isset($data["leave"]["user"]) && !isset($data["redo"])) {
            // Appending text to story
            $data["new-text"] = " " . $data["new-text"]; 

            DB::select("CALL updateStory(:newText, :gameId)", ["newText" => $data["new-text"], "gameId" => $data["game-id"]]); 
            Log::info("GAME #" . $data["game-id"] . ": Text appended"); 

            // Updating game turn
            if (($data["player-turn"] + 1) <= $data["turn-range"]) {
                DB::update("UPDATE GAME SET GAME_TURN = ? WHERE GAME_ID = ?", [$data["player-turn"] + 1, $data["game-id"]]); 

                return view('story')->with("newTurn", $data["player-turn"] + 1); 
            } else {
                DB::update("UPDATE GAME SET GAME_TURN = 1 WHERE GAME_ID = ?", [$data["game-id"]]); 

                return view('story')->with("newTurn", 1); 
            }
        } else if (isset($data["host-user"]) && isset($data["host-key"]) && isset($data["host-pass"])) {
            // Check if game key is valid
            if (array_intersect(str_split("1234567890"), str_split($data["host-key"]))) {
                $err = ["errCode" => "JH", "errMsg" => "Your room key cannot include numbers."]; 

                return view('story')->with("err", $err); 
            } else if ($data["host-limit"] < 1) {
                $err = ["errCode" => "JH", "errMsg" => "Your word limit cannot be less than 1."]; 

                return view('story')->with("err", $err); 
            }

            // Creating new story
            Log::info("Creating new story..."); 

            $data["host-key"] = strtoupper($data["host-key"]); 

            $gameId = DB::select("CALL createStory(:key, :pass, :user, :session, :title, :text, :limit, @gameId)", ["key" => $data["host-key"], "pass" => $data["host-pass"], "user" => $data["host-user"], "session" => $data["session"], "title" => $data["host-title"], "text" => $data["starter-text"], "limit" => $data["host-limit"]]);

            $gameId = json_decode(json_encode($gameId, true), true);  

            return view('story')->with("gameId", $gameId); 
        } else if (isset($data["start-game"])) {
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
        } else if (isset($data["wait-game"])) {
            Log::info("GAME #" . $data["wait-game"] . ": Waiting to begin"); 

            Log::info("DEBUG 1 --> Wait game is running"); 

            $gameRun = DB::select("SELECT GAME_RUN FROM GAME WHERE GAME_ID = ?", [$data["wait-game"]]); 
            $gameRun = json_decode(json_encode($gameRun, true), true)[0];

            // Sending back turn data for all players
            if ($gameRun["GAME_RUN"] == 1) {
                Log::info("DEBUG 2 --> GAME_RUN is now 1 (should only run once)"); 

                $turns = DB::select("SELECT PLAY_USER, PLAY_SESSION, PLAY_TURN FROM PLAYER WHERE GAME_ID = ? ORDER BY PLAY_TURN ASC;", [$data["wait-game"]]); 

                $turns = json_decode(json_encode($turns, true), true);

                return response()->json([
                    'html' => view('story', compact('turns'))->render()
                ]);
            }
        }

        return response()->json([
            'html' => view('story')->render()
        ]);
    }
}
