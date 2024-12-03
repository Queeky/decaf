<?php 
function getData() {
    $data = DB::select("SELECT * FROM TEST LIMIT 1"); 
    $data = json_decode(json_encode($data, true), true);

    return $data; 
}

if (isset($_POST["TEST_TIMESTAMP"])) {
    set_time_limit(30); 
    ignore_user_abort(false); 

    while (true) {
        $result = getData()[1]; 
    
        if (isset($result["TEST_TIMESTAMP"]) && $result["TEST_TIMESTAMP"] > $_POST["TEST_TIMESTAMP"]) {
            echo "<div style='width: 100%; text-align: center;'>{$result}</div>";
            break; 
        } 
        
        sleep(1); 
    }
}
?>