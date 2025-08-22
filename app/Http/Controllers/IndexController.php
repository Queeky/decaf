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

        if (!empty($data["website"]) || !empty($data["email"])) die(); // Quits if detects spam

        if (isset($data["bug-name"]) && isset($data["bug-msg"])) {
            $mail = new PHPMailer(true); // true enables exceptions

            try {
                //Server settings
                $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
                $mail->isSMTP();                                            //Send using SMTP
                $mail->Host       = 'smtp.gmail.com';                       //Set the SMTP server to send through
                $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
                $mail->Username   = 'ieatbugs.decaf@gmail.com';             //SMTP username
                $mail->Password   = 'pnbjvaviyviophap';                     //SMTP password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
                $mail->Port       = 465;    

                //Recipients
                $mail->setFrom('ieatbugs.decaf@gmail.com', 'Decaf');
                $mail->addAddress('ieatbugs.decaf@gmail.com');     
            
                $mail->Subject = "{$data["bug-name"]} sent you a bug";
                $mail->Body    = "{$data["bug-msg"]}";

                $mail->send();
            } catch(Exception $e) {
                Log::info("Message could not be sent. Mailer Error: {$mail->ErrorInfo}"); 
            }
        }

        return back(); 
    }
}
