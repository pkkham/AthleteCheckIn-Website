<?php
require_once 'include/DB_Functions.php';
$db = new DB_Functions();

if (isset($_POST['athlete']) && isset($_POST['password']) && isset($_POST['crn']) && isset($_POST['date']) && isset($_POST['absent']) && isset($_POST['tardy']) && isset($_POST['gps'])) {
    
    $email = $_POST['athlete'];
    $pass = $_POST['password'];
    
    $response = array("error" => FALSE);
    
    $user = $db->getUserByEmailAndPassword($email, $pass);
    
    if ($user != false) {
        $response["error"] = FALSE;
        $crn = $_POST['crn'];
        $absent = $_POST['absent'];
        $tardy = $_POST['tardy'];
        $gps = $_POST['gps'];
        $date = $_POST['date'];
        
        $record = $db->addPings($email, $crn, $date, $absent, $tardy, $gps);
        if ($record != false) {
            $response["error"] = FALSE;
            $response["msg"] = "Successfully stored!";
        } else {
            $response["error"] = TRUE;
            $response["error_msg"] = "Error storing record.";
        }
        echo json_encode($response);
    } else {
        // user is not found with the credentials
        $response["error"] = TRUE;
        $response["error_msg"] = "Login credentials are wrong. Please try again!";
        echo json_encode($response);
    }
}
?>