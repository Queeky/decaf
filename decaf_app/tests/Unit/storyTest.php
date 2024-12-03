<?php

namespace Tests\Unit;

// use PHPUnit\Framework\TestCase;
use Tests\TestCase; 

// Add IndexTest later that tests splash limit

class storyTest extends TestCase {

    // 1# Should not be able to submit GET form for join game if game info already set
    // #2 Joining game should set all expected vars
    // #3 gameAvail should be null if can not find game w/ info set from join form; otherwise, gameAvail should be set
    // #4 EVENTUALLY make pass, key, username, etc be lowercase -- test whether or not lowercase is working
    // #5 STORY_TURN_LIMIT should be an INT! 
    // #6 When creating story, should throw error if all inputs are not filled -- OR gives default value for some areas (word limit, starter text) but not others (pass, key, username)
    // #7 PLAY_SESSION should = SESSION_ID
    // #8 If gameId is set, player info should have host as 1; else, 0
    // #9 default turn should be 1 until specified (game starts)

    private function execute(array $session = [], array $get = [], array $post = []) { 
        // This MIGHT work but also might throw problems w/ controllers
        $_SESSION = $session; 
        $_GET = $get; 
        $_POST = $post; 

        ob_start(); 
        include("story.php"); 

        return ob_get_clean(); 
    }

    // Should not be able to join a game if game info is already set
    public function testGameInfoSet(): void {
        $_GET = ["key" => "TEST", "pass" => "testPass", "user" => "testUser"]; 
        $_SESSION = ["GAME_ID" => "1234"]; 

        include(dirname(__FILE__) . "/../../resources/views/story.php"); 

        // Fix later
        // https://stackoverflow.com/questions/47474554/laravel-phpunit-testing-get-with-parameters

        $this->assertTrue($_GET["pass"]); 
        $this->assertNotTrue($gameAvail); 
    }
    
}
