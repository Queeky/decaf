<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB; 

class StoryGetController extends Controller {
    function main(Request $request) {
        $readId = $request->get("read-more");

        // 1. User reads full story text
        if ($readId) {
            $readData = DB::select("SELECT STORY_TITLE, STORY_TEXT FROM STORY WHERE STORY_ID = ? LIMIT 1", [$readId]); 
            $readData = json_decode(json_encode($readData, true), true)[0];

            session(["STORY_TITLE" => $readData["STORY_TITLE"]]); 
            Log::info("User is reading STORY #" . $readId); // Testing only

            return view('story')->with("readText", $readData["STORY_TEXT"]); 
        }

        return view('story'); 
    }
}
