<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB; 

class StoryGetController extends Controller {
    function main(Request $request) {
        $readId = $request->get("admin-read");

        // 1. Admin reads full story text
        if (isset($readId)) {
            $adminRead = DB::select("SELECT STORY_TITLE, STORY_TEXT FROM STORY WHERE STORY_ID = ? LIMIT 1", [$readId]); 
            $adminRead = json_decode(json_encode($adminRead, true), true)[0];

            Log::info("Admin is reading STORY #" . $readId); 

            return view('story')->with("adminRead", $adminRead); // Go to session controller instead
        }

        return view('story'); 
    }
}
