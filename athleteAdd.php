<?php
require_once 'server_api/include/DB_Functions.php';
$db = new DB_Functions();
 
// json response array
$response = array("error" => FALSE);
    if (isset($_POST['firstname']) && isset($_POST['lastname']) && isset($_POST['course']) && isset($_POST['building']) && isset($_POST['room']) && isset($_POST['begintime']) && isset($_POST['endtime']) && isset($_POST['monday']) && isset($_POST['tuesday']) && isset($_POST['wednesday']) && isset($_POST['thursday']) && isset($_POST['friday'])) {
        //$db->storeUser("potato", "potato", "potato");
        $response["error"] = FALSE;
        $response["error_msg"] = "Received!";
        echo json_encode($response);
    } else {
        // required post params is missing
        //$db->storeUser("potato", "potato", "potato");
        $response["error"] = TRUE;
        $response["error_msg"] = "Required parameters are missing!";
        echo json_encode($response);
    }
?>