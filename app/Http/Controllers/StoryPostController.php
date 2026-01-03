<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\QueryException;
use DB; 

class StoryPostController extends Controller {
    function collectStory($id) { // Collects finished story
        // Collecting finished story
        $storyComplete = DB::select("SELECT STORY_ID, STORY_TITLE, STORY_TEXT FROM STORY WHERE STORY_ID = ?", [$id]); 
        $storyComplete = json_decode(json_encode($storyComplete, true), true)[0];
        session(["STORY_COMPLETE" => $storyComplete]); 
    }

    function main(Request $request) {
        $data = $request->post(); 

        // 1. Checks if wait-turn, wait-game, or wait-host polling is active
        if ((isset($data["wait-game"]) || isset($data["wait-turn"])) && !isset($data["leave"]) && !isset($data["start-game"])) {
            $waitGame = $request->input("wait-game"); 
            $waitTurn = $request->input("wait-turn"); 
            
            $id = $waitTurn ? $waitTurn : $waitGame; 

            $game = DB::select("SELECT GAME_RUN, GAME_TURN FROM GAME WHERE GAME_ID = ?", [$id]); 

            if ($game) {
                $game = json_decode(json_encode($game, true), true)[0];

                if ($waitTurn) { 
                    Log::info("GAME #{$waitTurn}: " . session("PLAYER.NAME") . " is waiting their turn"); 
                    session(["GAME.TURN" => $game["GAME_TURN"]]); 
        
                    return response()->json([
                        'html' => view('story')->render()
                    ]);
                } else if ($waitGame) { 
                    Log::info("GAME #{$waitGame}: Waiting to begin"); 

                    // Sending back turn data for all players if game is running
                    if (!session("PLAYER.HOST") && $game["GAME_RUN"] == 1) {
                        $turn = DB::select("SELECT P1.PLAY_TURN AS PLAY_TURN, P2.TURN_RANGE AS TURN_RANGE FROM (SELECT GAME_ID, PLAY_TURN FROM PLAYER WHERE GAME_ID = ? AND PLAY_USER = ? AND PLAY_SESSION = ?) AS P1 JOIN (SELECT GAME_ID, COUNT(PLAY_USER) AS TURN_RANGE FROM PLAYER GROUP BY GAME_ID) AS P2 ON P1.GAME_ID = P2.GAME_ID;", [$waitGame, session("PLAYER.NAME"), session("PLAYER.SESSION")]); 
                        $turn = json_decode(json_encode($turn, true), true)[0];

                        session(["PLAYER.TURN" => $turn["PLAY_TURN"]]); 
                        session(["GAME.TURN_RANGE" => $turn["TURN_RANGE"]]);
                        session(["GAME.RUN" => 1]); 
                    } 

                    return response()->json([
                        'html' => view('story')->render()
                    ]);
                }

                return response()->json([
                    'html' => view('story')->render()
                ]);
            } else { // Host left game
                Log::info("GAME #{$id}: Game ended, player kicked");

                $err = ["errCode" => "JP", "errMsg" => "Host has left the game."]; 

                DB::delete("DELETE FROM PLAYER WHERE PLAY_USER = ? AND PLAY_SESSION = ? AND GAME_ID = ?", [session("PLAYER.NAME"), session("PLAYER.SESSION"), $id]); 

                unset($_GET["join"]); 
                session()->forget(["GAME"]); 

                if ($waitTurn) {
                    $this->collectStory(session("STORY.ID")); 
                } else {
                    session()->forget(["PLAYER", "STORY"]); 
                }

                return response()->json([
                    'html' => view('story', compact("err"))->render()
                ]);
            }
        }

        // 2. Appends new text to story
        if (isset($data["new-text"]) && !isset($data["leave"]) && !isset($data["redo"])) {
            // Checks if text is too long
            $spaces = substr_count($data["new-text"], " "); 
            $underscores = substr_count($data["new-text"], "_"); 
            $wordCount = $spaces + $underscores + 1;
            $limit = session("STORY.TURN_INPUT_LIMIT"); 

            if ($wordCount > $limit) {
                Log::info("GAME #" . session("GAME.ID") . ": Message is too long"); 

                return view('story')->with("limitMessage", "Your message is too long! Write <strong>$limit word(s)</strong> or less.");
            }

            $data["new-text"] = " {$data["new-text"]}"; 

            $gameExists = DB::select("CALL updateStory(:newText, :gameId, @gameId)", ["newText" => $data["new-text"], "gameId" => session("GAME.ID")]); 

            if ($gameExists) {
                Log::info("GAME #" . session("GAME.ID") . ": Text appended"); 
                $turn = session("PLAYER.TURN");
                $turnRange = session("GAME.TURN_RANGE");  

                // Updating game turn
                if (($turn + 1) <= $turnRange) {
                    DB::update("UPDATE GAME SET GAME_TURN = ? WHERE GAME_ID = ?", [$turn + 1, session("GAME.ID")]); 

                    session(["GAME.TURN" => $turn + 1]);
                } else {
                    DB::update("UPDATE GAME SET GAME_TURN = 1 WHERE GAME_ID = ?", [session("GAME.ID")]); 

                    session(["GAME.TURN" => 1]);
                }

                return view('story'); 
            } else {
                Log::info("GAME #" . session("GAME.ID") . ": Player attempted to submit turn on game that no longer exists");

                DB::delete("DELETE FROM PLAYER WHERE PLAY_USER = ? AND PLAY_SESSION = ? AND GAME_ID = ?", [session("PLAYER.NAME"), session("PLAYER.SESSION"), session("GAME.ID")]); 

                unset($_GET["join"]); 
                session()->forget(["GAME"]); 

                $err = ["errCode" => " ", "errMsg" => "Host has left the game."];
                Log::info("\n\n" . serialize(session("STORY"))); 
                $this->collectStory(session("STORY.ID")); 

                return view('story')->with(compact("err")); 
            }
        } 

        // 3. Resets player's textarea
        if (isset($data["redo"])) return view('story'); 

        // 4. Private/public game login
        if (isset($data["join-key"]) || isset($data["join-pass"]) || isset($data["join-user"])) {
            if (isset($data["join-key"]) && isset($data["join-pass"]) && isset($data["join-user"])) {
                $data["join-key"] = strtoupper($data["join-key"]); 
                
                $checkPass = DB::select("SELECT GAME.GAME_ID, GAME.GAME_KEY, GAME.GAME_PASS, GAME.GAME_RUN, GAME.GAME_TURN, STORY.STORY_ID, STORY.STORY_TITLE, STORY.STORY_TEXT, STORY.STORY_TURN_VIEW_LIMIT, STORY.STORY_TURN_INPUT_LIMIT FROM GAME JOIN STORY ON GAME.GAME_ID = STORY.GAME_ID WHERE GAME_KEY = ? LIMIT 1", [$data["join-key"]]);
                $checkPass = json_decode(json_encode($checkPass, true), true);

                if ($checkPass) $avail = Hash::check($data["join-pass"], $checkPass[0]["GAME_PASS"]) ? $checkPass : null; 
            } else if (isset($data["join-user"]) && isset($data["join-public"])) {
                $avail = DB::select("SELECT GAME.GAME_ID, GAME.GAME_KEY, GAME.GAME_PASS, GAME.GAME_RUN, GAME.GAME_TURN, STORY.STORY_ID, STORY.STORY_TITLE, STORY.STORY_TEXT, STORY.STORY_TURN_VIEW_LIMIT, STORY.STORY_TURN_INPUT_LIMIT FROM GAME JOIN STORY ON GAME.GAME_ID = STORY.GAME_ID WHERE GAME_KEY = ? AND GAME_PASS IS NULL AND GAME_RUN = 0 ORDER BY RAND() LIMIT 1", ["RANDOM"]);

                $avail = isset($avail) ? json_decode(json_encode($avail, true), true) : null; 
            } else {
                $err = ["errCode" => "JP", "errMsg" => "You must fill out all fields."]; 
                return view('story')->with("err", $err); 
            }

            // Code should only reach here if performed DB check for public/private game
            if (isset($avail) && $avail) {
                if ($avail[0]["GAME_RUN"] == 0) {
                    $avail = $avail[0]; 

                    $gameData = [
                        "ID" => $avail["GAME_ID"], 
                        "KEY" => $avail["GAME_KEY"], 
                        "PASS" => isset($avail["GAME_PASS"]) ? $avail["GAME_PASS"] : " ", 
                        "RUN" => 0, 
                        "TURN" => $avail["GAME_TURN"]
                    ]; 
                    $storyData = [
                        "ID" => $avail["STORY_ID"], 
                        "TITLE" => $avail["STORY_TITLE"], 
                        "TURN_VIEW_LIMIT" => $avail["STORY_TURN_VIEW_LIMIT"], 
                        "TURN_INPUT_LIMIT" => $avail["STORY_TURN_INPUT_LIMIT"]
                    ]; 
                    $playerData = [
                        "NAME" => $data["join-user"], 
                        "TURN" => 0, 
                        "HOST" => false, 
                        "SESSION" => session("SESSION_ID")
                    ]; 

                    session(["GAME" => $gameData]); 
                    session(["STORY" => $storyData]); 
                    session(["PLAYER" => $playerData]); 

                    DB::insert("INSERT INTO PLAYER (PLAY_USER, GAME_ID, PLAY_SESSION) VALUES (?, ?, ?)", [$data["join-user"], session("GAME.ID"), session("PLAYER.SESSION")]);

                    Log::info("GAME #{$avail['GAME_ID']}: {$data["join-user"]} joined"); 
                    return view('story'); 
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

        // 5. User leaves game (host or player)
        if (isset($data["leave"])) {
            // Collecting finished story
            $gameId = $data["leave"]; 
            $this->collectStory(session("STORY.ID")); 

            if (session("PLAYER.HOST")) {
                // Host left, remove game
                DB::select("CALL endGame(?)", [$gameId]); 

                Log::info("Story finished! --> GAME #{$gameId}"); 
            } else {
                // Player left, remove player from game
                DB::delete("DELETE FROM PLAYER WHERE PLAY_USER = ? AND PLAY_SESSION = ? AND GAME_ID = ?", [session("PLAYER.NAME"), session("PLAYER.SESSION"), $gameId]); 
            }

            Log::info("GAME #{$gameId}: " . session("PLAYER.NAME") . " left"); 

            unset($_GET["join"]); 
            session()->forget(["GAME"]); 

            return view('story'); 
        } 

        // 6. Player leaves story result screen
        if (isset($data["leave-story-result"])) {
            session()->forget(["PLAYER", "STORY", "STORY_COMPLETE"]); 
            return view('story'); 
        }

        // 7. Player downloads story as .txt file
        if (isset($data["file-download"])) { 
            $title = session("STORY_COMPLETE.STORY_TITLE"); 
            $text = session("STORY_COMPLETE.STORY_TEXT"); 

            return Response::streamDownload(
                function() use ($title, $text) {
                    echo "{$title}\n\n{$text}"; 
                }, 
                "{$title}.txt", 
                [
                    'Content-Type' => 'text/plain', 
                    'Content-Disposition' => 'attachment; filename="' . $title . '"'
                ]
            ); 
        }

        // 8. Host creates a new story
        if (isset($data["host-user"]) || isset($data["host-key"]) || isset($data["host-pass"]) || isset($data["make-public"]) || isset($data["host-title"]) || isset($data["host-view-limit"]) || isset($data["host-input-limit"]) || isset($data["starter-text"])) {
            if (isset($data["host-user"]) && isset($data["make-public"]) && isset($data["host-title"]) && isset($data["host-view-limit"]) && isset($data["host-input-limit"]) && isset($data["starter-text"])) {
                // Check if key is valid
                if (array_intersect(str_split("!@#$%^&*()-_+={}[]|\\/<>,.;:\"'~`"), str_split($data["host-key"]))) {
                    $err = ["errCode" => "JH", "errMsg" => "Your room key cannot include special characters."]; 
                } else if ($data["make-public"] == "n" && !isset($data["host-key"])) {
                    $err = ["errCode" => "JH", "errMsg" => "Private games must have a room key."]; 
                } else if ($data["make-public"] == "n" && !isset($data["host-pass"])) {
                    $err = ["errCode" => "JH", "errMsg" => "Private games must have a password."]; 
                } else if ($data["host-view-limit"] < 1 || $data["host-input-limit"] < 1) {
                    $err = ["errCode" => "JH", "errMsg" => "Your view/input limit cannot be less than 1."]; 
                } 

                // Checks if any errors were set above
                if (isset($err)) return view('story')->with("err", $err); 

                Log::info("Creating new story...");  

                $unhashedPass = $data["host-pass"]; 
                $data["host-pass"] = ($data["make-public"] == "n") ? Hash::make($data["host-pass"]) : null; 
                $data["host-key"] = ($data["make-public"] == "n") ? strtoupper($data["host-key"]) : "RANDOM";

                try {
                    $results = DB::select("CALL createStory(:key, :pass, :user, :session, :title, :text, :viewLimit, :inputLimit, @gameId, @storyId)", ["key" => $data["host-key"], "pass" => $data["host-pass"], "user" => $data["host-user"], "session" => $data["session"], "title" => $data["host-title"], "text" => $data["starter-text"], "viewLimit" => $data["host-view-limit"], "inputLimit" => $data["host-input-limit"]]);
                    $results = json_decode(json_encode($results, true), true)[0];
                } catch(QueryException $e) {
                    $err = ["errCode" => "JH", "errMsg" => "This key already exists."];
                    return view('story')->with("err", $err); 
                }
                
                $gameData = [
                    "ID" => $results["@gameId"], 
                    "KEY" => ($data["make-public"] == "n") ? $data["host-key"] : "RANDOM", 
                    "PASS" => ($data["make-public"] == "n") ? $unhashedPass : " ", 
                    "RUN" => 0, 
                    "TURN" => 1
                ]; 
                $storyData = [
                    "ID" => $results["@storyId"], 
                    "TITLE" => $data["host-title"], 
                    "TURN_VIEW_LIMIT" => $data["host-view-limit"], 
                    "TURN_INPUT_LIMIT" => $data["host-input-limit"]
                ]; 
                $playerData = [
                    "NAME" => $data["host-user"], 
                    "TURN" => 0, 
                    "HOST" => true, 
                    "SESSION" => session("SESSION_ID")
                ]; 

                session(["GAME" => $gameData]); 
                session(["STORY" => $storyData]); 
                session(["PLAYER" => $playerData]); 

                Log::info("Story created! --> GAME #{$results["@gameId"]}"); 
            } else {
                $err = ["errCode" => "JH", "errMsg" => "You must fill out all necessary fields."]; 
    
                return view('story')->with("err", $err); 
            }
            return view('story'); 
        }

        // 9. Host starts game
        if (isset($data["start-game"])) {
            // Starting game
            Log::info("GAME #{$data["start-game"]}: Assigning player turns"); 

            $sql = "SET @count := 0; "; 
            $sql .= "UPDATE PLAYER SET PLAY_TURN = @count := @count + 1 "; 
            $sql .= "WHERE GAME_ID = {$data["start-game"]} ORDER BY RAND();"; 
            DB::unprepared($sql); 

            $turn = DB::select("SELECT P1.PLAY_TURN AS PLAY_TURN, P2.TURN_RANGE AS TURN_RANGE FROM (SELECT GAME_ID, PLAY_TURN FROM PLAYER WHERE GAME_ID = ? AND PLAY_USER = ? AND PLAY_SESSION = ?) AS P1 JOIN (SELECT GAME_ID, COUNT(PLAY_USER) AS TURN_RANGE FROM PLAYER GROUP BY GAME_ID) AS P2 ON P1.GAME_ID = P2.GAME_ID;", [$data["start-game"], session("PLAYER.NAME"), session("PLAYER.SESSION")]); 
            $turn = json_decode(json_encode($turn, true), true)[0];

            Log::info("GAME #{$data["start-game"]}: Starting game"); 
            DB::update("UPDATE GAME SET GAME_RUN = 1 WHERE GAME_ID = ?", [$data["start-game"]]); 

            session(["PLAYER.TURN" => $turn["PLAY_TURN"]]); 
            session(["GAME.TURN_RANGE" => $turn["TURN_RANGE"]]);
            session(["GAME.RUN" => 1]); 

            return view('story'); 
        } 

        // 10. Admin deletes story
        if (isset($data["admin-delete"])) {
            $id = isset($data["delete-story"]) ? [$data["delete-story"], "host"] : [$data["admin-delete"], "admin"]; 

            DB::delete("DELETE FROM STORY WHERE STORY_ID = ?", [$id[0]]); 
            Log::info("STORY #{$id[0]} was removed by {$id[1]}"); 

            session()->forget(["STORY", "PLAYER", "STORY_COMPLETE"]); 
            return view('story'); 
        }

        // 11. Host publishes story
        if (isset($data["publish-story"])) {
            DB::update("UPDATE STORY SET STORY_PUBLISH = 1 WHERE STORY_ID = ?", [$data["publish-story"]]); 

            Log::info("STORY #{$data["publish-story"]} was published"); 

            session()->forget(["STORY", "PLAYER", "STORY_COMPLETE"]); 
            return view('story'); 
        }

        return view('story'); 
    }
}
