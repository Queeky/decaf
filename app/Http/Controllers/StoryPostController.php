<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StoryPostController extends Controller {
    function main(Request $request) {
        $data = $request->post(); 

        // 1. Checks if wait-turn, wait-game, or wait-host polling is active
        if ((isset($data["wait-game"])) || (!isset($data["start-game"]) && (isset($data["wait-turn"]))))  {
            $waitGame = $request->input("wait-game"); 
            $waitTurn = $request->input("wait-turn"); 
            
            $id = $waitTurn ? $waitTurn : $waitGame; 

            $game = DB::select("SELECT GAME_RUN, GAME_TURN FROM GAME WHERE GAME_ID = ?", [$id]); 

            if ($game) {
                $game = json_decode(json_encode($game, true), true)[0];

                if ($waitTurn) { 
                    Log::info("GAME #$waitTurn: " . session("PLAYER")["NAME"] . " is waiting their turn"); 
        
                    $updated = [...session("GAME"), "TURN" => $game["GAME_TURN"]]; 
                    session(["GAME" => $updated]); 
        
                    return response()->json([
                        'html' => view('story')->render()
                    ]);
                } else if ($waitGame) { 
                    Log::info("GAME #$waitGame: Waiting to begin"); 

                    // Sending back turn data for all players if game is running
                    // NOTE: Why can't this also work for the host?
                    // I think because host gets it somewhere else when they begin the game
                    if (!session("PLAYER")["HOST"] && $game["GAME_RUN"] == 1) {
                        $turn = DB::select("SELECT PLAY_TURN FROM PLAYER WHERE GAME_ID = ? AND PLAY_SESSION = ? ORDER BY PLAY_TURN ASC;", [$waitGame, session("SESSION_ID")]); 
                        $turn = json_decode(json_encode($turn, true), true)[0];

                        $turnRange = DB::select("SELECT COUNT(PLAY_USER) AS TURN_RANGE FROM PLAYER WHERE GAME_ID = ?;", [$waitGame]); 
                        $turnRange = json_decode(json_encode($turnRange, true), true)[0];

                        $player = [...session("PLAYER"), "TURN" => $turn["PLAY_TURN"]]; 
                        session(["PLAYER" => $player]); 

                        $updated = [...session("GAME"), "TURN_RANGE" => $turnRange["TURN_RANGE"], "RUN" => 1]; 
                        session(["GAME" => $updated]); 
                    } 

                    return response()->json([
                        'html' => view('story')->render()
                    ]);
                }

                return response()->json([
                    'html' => view('story')->render()
                ]);
            } else { // Host left game
                Log::info("GAME #$id: Game ended, player kicked");

                $err = ["errCode" => "JP", "errMsg" => "Host has left the game."]; 
                $storyComplete = false; 

                if ($waitTurn) {
                    // Collecting finished story
                    $storyComplete = DB::select("SELECT STORY_ID, STORY_TITLE, STORY_TEXT FROM STORY WHERE STORY_ID = ?", [session("STORY")["ID"]]); 
                    $storyComplete = json_decode(json_encode($storyComplete, true), true)[0];
                } 

                return response()->json([
                    'html' => view('story', compact("err", "storyComplete"))->render()
                ]);
            }
        }

        // 2. Appends new text to story
        if (isset($data["new-text"])) {
            // Checks if text is too long
            $spaces = substr_count($data["new-text"], " "); 
            $underscores = substr_count($data["new-text"], "_"); 
            $wordCount = $spaces + $underscores + 1;
            $limit = session("STORY")["LIMIT"]; 

            if ($wordCount > $limit) {
                Log::info("GAME #" . session("GAME")["ID"] . ": Message is too long"); 

                return view('story')->with("limitMessage", "Your message is too long! Write <strong>$limit word(s)</strong> or less.");
            }

            $data["new-text"] = ` {$data["new-text"]}`; 

            $gameExists = DB::select("CALL updateStory(:newText, :gameId, @gameId)", ["newText" => $data["new-text"], "gameId" => session("GAME")["ID"]]); 

            if ($gameExists) {
                Log::info("GAME #" . session("GAME")["ID"] . ": Text appended"); 
                $turn = session("PLAYER")["TURN"];
                $turnRange = session("GAME")["TURN_RANGE"];  

                // Updating game turn
                if (($turn + 1) <= $turnRange) {
                    DB::update("UPDATE GAME SET GAME_TURN = ? WHERE GAME_ID = ?", [$turn + 1, session("GAME")["ID"]]); 
                    // session(["GAME.TURN" => $turn + 1]); 
                    // NOTE: Probably not necessary
                } else {
                    DB::update("UPDATE GAME SET GAME_TURN = 1 WHERE GAME_ID = ?", [session("GAME")["ID"]]); 
                    // session(["GAME.TURN" => $turn]); 
                }

                return view('story'); 
            } else {
                Log::info("GAME #" . session("GAME")["ID"] . ": Player attempted to submit turn on game that no longer exists");

                $err = ["errCode" => " ", "errMsg" => "Host has left the game."];

                // Collecting finished story
                $storyComplete = DB::select("SELECT STORY_ID, STORY_TITLE, STORY_TEXT FROM STORY WHERE STORY_ID = ?", [session("STORY")["ID"]]); 
                $storyComplete = json_decode(json_encode($storyComplete, true), true)[0];

                return view('story')->with(compact("err", "storyComplete")); 
            }
        } 

        // 3. Resets player's textarea
        if (isset($data["redo"])) return view('story'); 

        // 4. Private/public game login
        if (isset($data["join-key"]) || isset($data["join-pass"]) || isset($data["join-user"])) {
            if (isset($data["join-key"]) && isset($data["join-pass"]) && isset($data["join-user"])) {
                $data["join-key"] = strtoupper($data["join-key"]); 
                
                $checkPass = DB::select("SELECT GAME.GAME_ID, GAME.GAME_KEY, GAME.GAME_PASS, GAME.GAME_RUN, GAME.GAME_TURN, STORY.STORY_ID, STORY.STORY_TITLE, STORY.STORY_TEXT, STORY.STORY_TURN_LIMIT FROM GAME JOIN STORY ON GAME.GAME_ID = STORY.GAME_ID WHERE GAME_KEY = ? LIMIT 1", [$data["join-key"]]);
                $checkPass = json_decode(json_encode($checkPass, true), true);

                if ($checkPass) {
                    $avail = Hash::check($data["join-pass"], $checkPass[0]["GAME_PASS"]) ? $checkPass : null; 
                } 
            } else if (isset($data["join-user"]) && isset($data["join-public"])) {
                $avail = DB::select("SELECT GAME.GAME_ID, GAME.GAME_KEY, GAME.GAME_PASS, GAME.GAME_RUN, GAME.GAME_TURN, STORY.STORY_ID, STORY.STORY_TITLE, STORY.STORY_TEXT, STORY.STORY_TURN_LIMIT FROM GAME JOIN STORY ON GAME.GAME_ID = STORY.GAME_ID WHERE GAME_KEY = ? AND GAME_PASS IS NULL AND GAME_RUN = 0 ORDER BY RAND() LIMIT 1", ["RANDOM"]);

                $avail = isset($avail) ? json_decode(json_encode($avail, true), true) : null; 
            } else {
                $err = ["errCode" => "JP", "errMsg" => "You must fill out all fields."]; 

                return view('story')->with("err", $err); 
            }

            // Code should only reach here if performed DB check for public/private game
            if (isset($avail) && $avail) {
                if ($avail[0]["GAME_RUN"] == 0) {
                    $joinUser = $data["join-user"]; 

                    Log::info("GAME #$avail[0]['GAME_ID']: $joinUser joined"); 

                    return view('story')->with(compact("avail", "joinUser"));
                } else if ($avail[0]["GAME_RUN"] == 1) {
                    $err = ["errCode" => "JP", "errMsg" => "This game has already begun."]; 

                    return view('story')->with("err", $err);
                } 
            } else {
                Log::info("Login failed"); 

                $err = isset($data["join-public"]) ? ["errCode" => "JR", "errMsg" => "There are currently no public games."] : ["errCode" => "JP", "errMsg" => "This game does not exist."]; 

                return view('story')->with("err", $err);
            }
        }

    }
}
