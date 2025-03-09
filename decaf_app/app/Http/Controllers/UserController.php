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
            Log::info("Beta player login failure (gullible vers.)"); 
            return view('login')->with("message", ["Haha get rekt"]); 
        } else if (!isset($data["pass"])) {
            Log::info("Beta player login failure"); 
            return view('login')->with("message", ["To solve the password, you 1) must enter a password, and 2) the password must be correct. I hope this helps."]); 
        }

        $pass = (isset($data["pass"])) ?  $data["pass"] . "?" : ""; 

        Log::info("Beta player login failure"); 

        return view('login')->with("message", ["No", "Try again", "That is incorrect", "So close, yet so far", "Have you tried '1234'", "Hello I'm your cousin James and I'm stuck in Mexico, please send your credit card # so I can book a flight home", "Wrong", "Could you rephrase that?", "Say pretty please", "Come back later", "What did you say? <strong>{$pass}</strong>"]); 
    }

    public function storyGet() {
        $readId = request()->get("admin-read");

        // 1. Admin reads full story text
        if (isset($readId)) {
            $adminRead = DB::select("SELECT STORY_TITLE, STORY_TEXT FROM STORY WHERE STORY_ID = ? LIMIT 1", [$readId]); 
            $adminRead = json_decode(json_encode($adminRead, true), true)[0];

            Log::info("Admin is reading STORY #" . $readId); 

            return view('story')->with("adminRead", $adminRead); 
        }

        return view('story'); 
    }

    public function storyPost() {
        $data = request()->post(); 

        // 1. Private/public game login
        if (isset($data["join-key"]) || isset($data["join-pass"]) || isset($data["join-user"])) {
            if (isset($data["join-key"]) && isset($data["join-pass"]) && isset($data["join-user"])) {
                $data["join-key"] = strtoupper($data["join-key"]); 

                $avail = DB::select("SELECT GAME.GAME_ID, GAME.GAME_KEY, GAME.GAME_PASS, GAME.GAME_RUN, GAME.GAME_TURN, STORY.STORY_TITLE, STORY.STORY_TEXT, STORY.STORY_TURN_LIMIT FROM GAME JOIN STORY ON GAME.GAME_ID = STORY.GAME_ID WHERE GAME_KEY = ? AND GAME_PASS = ? LIMIT 1", [$data["join-key"], $data["join-pass"]]);
            } else if (isset($data["join-user"]) && isset($data["join-public"])) {
                $avail = DB::select("SELECT GAME.GAME_ID, GAME.GAME_KEY, GAME.GAME_PASS, GAME.GAME_RUN, GAME.GAME_TURN, STORY.STORY_TITLE, STORY.STORY_TEXT, STORY.STORY_TURN_LIMIT FROM GAME JOIN STORY ON GAME.GAME_ID = STORY.GAME_ID WHERE GAME_KEY = ? AND GAME_PASS IS NULL AND GAME_RUN = 0 ORDER BY RAND() LIMIT 1", ["RANDOM"]);
            } else {
                $err = ["errCode" => "JP", "errMsg" => "You must fill out all fields."]; 

                return view('story')->with("err", $err); 
            }

            // Code should only reach here if performed DB check for public/private game
            if (isset($avail)) {
                $avail = json_decode(json_encode($avail, true), true);
                $joinUser = $data["join-user"]; 

                if ($avail && ($avail[0]["GAME_RUN"] == 0)) {
                    Log::info("GAME #" . $avail[0]["GAME_ID"] . ": " . $joinUser . " joined"); 

                    return view('story')->with(compact("avail", "joinUser"));
                } else if ($avail && ($avail[0]["GAME_RUN"] == 1)) {
                    $err = ["errCode" => "JP", "errMsg" => "This game has already begun."]; 

                    return view('story')->with("err", $err);
                } else {
                    $err = isset($data["join-public"]) ? ["errCode" => "JR", "errMsg" => "There are currently no public games."] : ["errCode" => "JP", "errMsg" => "This game does not exist."]; 

                    return view('story')->with("err", $err);
                }
            }
        }

        // 2. Resets player's textarea
        if (isset($data["redo"])) {
            return view('story');
        } 

        // 3. User leaves game (host or player)
        if (isset($data["leave"]["user"])) {
            if (isset($data["leave"]["host"])) {
                // Host left, remove game
                DB::select("CALL endGame(?)", [$data["leave"]["id"]]); 

                Log::info("GAME #" . $data["leave"]["id"] . " ended"); 
            } else {
                // Player left, remove player from game
                DB::delete("DELETE FROM PLAYER WHERE PLAY_USER = ? AND GAME_ID = ?", [$data["leave"]["user"], $data["leave"]["id"]]); 
            }

            Log::info("GAME #" . $data["leave"]["id"] . ": " . $data["leave"]["user"] . " left"); 

            return view('story')->with("leftGame", true); 
        } 

        // 4. Checks if wait-turn, wait-game, or wait-host polling is active
        if (!isset($data["start-game"]) && (isset($data["wait-turn"]) || isset($data["wait-game"]))) { 
            $id = isset($data["wait-turn"]) ? $data["wait-turn"] : $data["wait-game"]; 
            $gameInfo = null; 

            $gameInfo = DB::select("SELECT GAME_RUN, GAME_TURN FROM GAME WHERE GAME_ID = ?", [$id]); 

            if ($gameInfo) {
                $gameInfo = json_decode(json_encode($gameInfo, true), true)[0];

                if (isset($data["wait-turn"])) {
                    Log::info("GAME #" . $data["wait-turn"] . ": " . $data["wait-player"] . " is waiting their turn"); 
        
                    $gameTurn = $gameInfo["GAME_TURN"]; 
        
                    return response()->json([
                        'html' => view('story', compact('gameTurn'))->render()
                    ]);
                } else if (isset($data["wait-game"])) {
                    Log::info("GAME #" . $data["wait-game"] . ": Waiting to begin"); 

                    // Sending back turn data for all players if game is running
                    if (!isset($data["wait-host"]) && $gameInfo["GAME_RUN"] == 1) {
                        $turns = DB::select("SELECT PLAY_USER, PLAY_SESSION, PLAY_TURN FROM PLAYER WHERE GAME_ID = ? ORDER BY PLAY_TURN ASC;", [$data["wait-game"]]); 

                        $turns = json_decode(json_encode($turns, true), true);

                        return response()->json([
                            'html' => view('story', compact('turns'))->render()
                        ]);
                    } else {
                        return response()->json([
                            'html' => view('story')->render()
                        ]);
                    }
                }

                Log::info("Uh oh spaghetti-o, storyPost #4 is broken"); 
                return response()->json([
                    'html' => view('story')->render()
                ]);
            } else {
                Log::info("GAME #" . $data["wait-game"] . ": Game ended, player kicked");

                $err = ["errCode" => "JP", "errMsg" => "Host has left the game."]; 
                $leftGame = true; 

                return response()->json([
                    'html' => view('story', compact("err", "leftGame"))->render()
                ]);
            }
        }

        // 5. Player's text is too long
        if (!isset($data["leave"]["user"]) && isset($data["new-text"]) && ((substr_count($data["new-text"], " ") + 1) > $data["turn-limit"])) {
            Log::info("GAME #" . $data["game-id"] . ": Message is too long"); 

            return view('story')->with("limitMessage", "Your message is too long! Write <strong>{$data["turn-limit"]} word(s)</strong> or less.");
        } 
        
        // 6. Appends new text to story
        if (isset($data["new-text"]) && !isset($data["leave"]["user"]) && !isset($data["redo"])) {
            $data["new-text"] = " " . $data["new-text"]; 

            $gameExists = DB::select("CALL updateStory(:newText, :gameId, @gameId)", ["newText" => $data["new-text"], "gameId" => $data["game-id"]]); 

            if ($gameExists) {
                Log::info("GAME #" . $data["game-id"] . ": Text appended"); 

                // Updating game turn
                if (($data["player-turn"] + 1) <= $data["turn-range"]) {
                    DB::update("UPDATE GAME SET GAME_TURN = ? WHERE GAME_ID = ?", [$data["player-turn"] + 1, $data["game-id"]]); 

                    return view('story')->with("newTurn", $data["player-turn"] + 1); 
                } else {
                    DB::update("UPDATE GAME SET GAME_TURN = 1 WHERE GAME_ID = ?", [$data["game-id"]]); 

                    return view('story')->with("newTurn", 1); 
                }
            } else {
                Log::info("GAME #" . $data["game-id"] . ": Player attempted to submit turn on game that no longer exists");

                $err = ["errCode" => "JP", "errMsg" => "Host has left the game."];
                $leftGame = true; 

                return view('story')->with(compact("err", "leftGame")); 
            }
        } 
        
        // 7. Host creates a new story
        if (isset($data["host-user"]) || isset($data["host-key"]) || isset($data["host-pass"]) || isset($data["make-public"]) || isset($data["host-title"]) || isset($data["host-limit"]) || isset($data["starter-text"])) {
            if (isset($data["host-user"]) && isset($data["make-public"]) && isset($data["host-title"]) && isset($data["host-limit"]) && isset($data["starter-text"])) {
                // Check if key is valid
                if (array_intersect(str_split("1234567890"), str_split($data["host-key"]))) {
                    $err = ["errCode" => "JH", "errMsg" => "Your room key cannot include numbers."]; 
    
                    return view('story')->with("err", $err); 
                } 

                if ($data["make-public"] == "n" && !isset($data["host-key"])) {
                    $err = ["errCode" => "JH", "errMsg" => "Private games must have a room key."]; 
    
                    return view('story')->with("err", $err);
                }

                if ($data["make-public"] == "n" && !isset($data["host-pass"])) {
                    $err = ["errCode" => "JH", "errMsg" => "Private games must have a password."]; 
    
                    return view('story')->with("err", $err);
                }

                if ($data["host-limit"] < 1) {
                    $err = ["errCode" => "JH", "errMsg" => "Your word limit cannot be less than 1."]; 
    
                    return view('story')->with("err", $err); 
                }

                // Check if key is already in use
                // Eventually incorporate this in main select below
                if ($data["make-public"] == "n") {
                    $exists = DB::select("SELECT GAME_ID FROM GAME WHERE GAME_KEY = ?", [$data["host-key"]]); 

                    if ($exists) {
                        $err = ["errCode" => "JH", "errMsg" => "This key already exists."];

                        return view('story')->with("err", $err);
                    }
                }
    
                Log::info("Creating new story..."); 

                // If private, uppercase submitted key; if public, key becomes RANDOM
                $data["host-key"] = ($data["make-public"] == "n") ? strtoupper($data["host-key"]) : "RANDOM"; 
    
                $gameId = DB::select("CALL createStory(:key, :pass, :user, :session, :title, :text, :limit, @gameId)", ["key" => $data["host-key"], "pass" => $data["host-pass"], "user" => $data["host-user"], "session" => $data["session"], "title" => $data["host-title"], "text" => $data["starter-text"], "limit" => $data["host-limit"]]);
    
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

        // 9. Admin deletes completed story
        if (isset($data["admin-delete"])) {
            DB::delete("DELETE FROM STORY WHERE STORY_ID = ?", [$data["admin-delete"]]); 

            Log::info("STORY #" . $data["admin-delete"] . " was removed by admin"); 

            return view('story'); 
        }

        return view('story'); 
    }
}
