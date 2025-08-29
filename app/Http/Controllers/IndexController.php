<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use DB;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

class IndexController extends Controller {
    function get(Request $request) {
        $storyId = $request->get("read-more-story");

        // 1. User reads full story text
        if ($storyId) {
            $readData = DB::select("SELECT STORY_TITLE, STORY_TEXT FROM STORY WHERE STORY_ID = ? LIMIT 1", [$storyId]); 
            $readData = json_decode(json_encode($readData, true), true)[0];

            session(["STORY.TITLE" => $readData["STORY_TITLE"]]); 

            return view('index')->with("readText", $readData["STORY_TEXT"]); 
        }

        return view('index'); 
    }

    function post(Request $request) {
        $data = $request->post(); 

        return back(); 
    }
}
