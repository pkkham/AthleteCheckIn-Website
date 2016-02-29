<?php
require_once 'include/DB_Functions.php';
$db = new DB_Functions();
 
// json response array
$response = array("error" => FALSE);
 
if (isset($_POST['email']) && isset($_POST['password'])) {
 
    // receiving the post params
    $email = $_POST['email'];
    $password = $_POST['password'];
 
    // get the user by email and password
    $user = $db->getUserByEmailAndPassword($email, $password);
 
    if ($user != false) {
        // use is found
        $response["error"] = FALSE;
        $response["uid"] = $user["unique_id"];
        $response["user"]["name"] = $user["name"];
        $response["user"]["email"] = $user["email"];
        $response["user"]["counselor"] = $user["counselor"];
        $response["user"]["sport"] = $user["sports"];
        $response["user"]["courses"] = $user["courses"];
        
        //Separate courses into an array
        $courses = explode("~", $user["courses"]);
        
        //Get course information for each course
        foreach($courses as &$value) {
            $course = $db->getCourse($value);
            if ($course != false) {
                //Global
                $response["user"][$value]["crn"] = $course["crn"];
                //Monday   
                $response["user"][$value]["monday"]["start_time"] = $course["m_start_time"];
                $response["user"][$value]["monday"]["end_time"] = $course["m_end_time"];
                $response["user"][$value]["monday"]["room"] = $course["m_room"];
                //Tuesday
                $response["user"][$value]["tuesday"]["start_time"] = $course["tu_start_time"];
                $response["user"][$value]["tuesday"]["end_time"] = $course["tu_end_time"];
                $response["user"][$value]["tuesday"]["room"] = $course["tu_room"];
                //Wednesday
                $response["user"][$value]["wednesday"]["start_time"] = $course["w_start_time"];
                $response["user"][$value]["wednesday"]["end_time"] = $course["w_end_time"];
                $response["user"][$value]["wednesday"]["room"] = $course["w_room"];
                //Thursday
                $response["user"][$value]["thursday"]["start_time"] = $course["th_start_time"];
                $response["user"][$value]["thursday"]["end_time"] = $course["th_end_time"];
                $response["user"][$value]["thursday"]["room"] = $course["th_room"];
                //Friday
                $response["user"][$value]["friday"]["start_time"] = $course["f_start_time"];
                $response["user"][$value]["friday"]["end_time"] = $course["f_end_time"];
                $response["user"][$value]["friday"]["room"] = $course["f_room"];
            }
        }
        unset($value);
        echo json_encode($response);
    } else {
        // user is not found with the credentials
        $response["error"] = TRUE;
        $response["error_msg"] = "Login credentials are wrong. Please try again!";
        echo json_encode($response);
    }
} else {
    // required post params is missing
    $response["error"] = TRUE;
    $response["error_msg"] = "Required parameters email or password is missing!";
    echo json_encode($response);
}
?>