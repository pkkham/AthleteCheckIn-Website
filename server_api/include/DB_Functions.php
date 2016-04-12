<?php
 
class DB_Functions {
 
    private $conn;
 
    // constructor
    function __construct() {
        require_once dirname(__FILE__).'/DB_Connect.php';
        // connecting to database
        $db = new Db_Connect();
        $this->conn = $db->connect();
    }
 
    // destructor
    function __destruct() {
         
    }
 
    /**
     * Storing new user
     * returns user details
     */
    public function storeUser($name, $email, $password) {
        $uuid = uniqid('', true);
        $hash = $this->hashSSHA($password);
        $encrypted_password = $hash["encrypted"]; // encrypted password
        $salt = $hash["salt"]; // salt
        $sports = "Football";
        $courses = "Comp 4710";
        $counselor = "Bob T Builder";
 
        $stmt = $this->conn->prepare("INSERT INTO athletes(unique_id, name, email, encrypted_password, salt, sports, courses, counselor) VALUES(?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $uuid, $name, $email, $encrypted_password, $salt, $sports, $courses, $counselor);
        $result = $stmt->execute();
        $stmt->close();
 
        // check for successful store
        if ($result) {
            $stmt = $this->conn->prepare("SELECT * FROM athletes WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();
 
            return $user;
        } else {
            return false;
        }
    }
 
    /**
     * Get user by email and password
     */
    public function getUserByEmailAndPassword($email, $password) {
 
        $stmt = $this->conn->prepare("SELECT * FROM athletes WHERE email = ?");
 
        $stmt->bind_param("s", $email);
 
        if ($stmt->execute()) {
            $user = $stmt->get_result()->fetch_assoc();
            
            //Check the users password
            $hash = $this->checkhashSSHA($user["salt"], $password);
            if ($hash != $user["encrypted_password"]) {
                return NULL;
            }
            
            $stmt->close();
            return $user;
        } else {
            return NULL;
        }
    }
 
    /**
     * Check user is existed or not
     */
    public function isUserExisted($email) {
        $stmt = $this->conn->prepare("SELECT email from athletes WHERE email = ?");
 
        $stmt->bind_param("s", $email);
 
        $stmt->execute();
 
        $stmt->store_result();
 
        if ($stmt->num_rows > 0) {
            // user existed 
            $stmt->close();
            return true;
        } else {
            // user not existed
            $stmt->close();
            return false;
        }
    }
 
    /**
     * Encrypting password
     * @param password
     * returns salt and encrypted password
     */
    public function hashSSHA($password) {
 
        $salt = sha1(rand());
        $salt = substr($salt, 0, 10);
        $encrypted = base64_encode(sha1($password . $salt, true) . $salt);
        $hash = array("salt" => $salt, "encrypted" => $encrypted);
        return $hash;
    }
 
    /**
     * Decrypting password
     * @param salt, password
     * returns hash string
     */
    public function checkhashSSHA($salt, $password) {
 
        $hash = base64_encode(sha1($password . $salt, true) . $salt);
 
        return $hash;
    }
 
    /**
     * Get a course by its name. Return null if course doesn't exist
     **/
    public function getCourse($courseName) {
        $stmt = $this->conn->prepare("SELECT * FROM courses WHERE crn = ?");
        
        $stmt->bind_param("s", $courseName);
        
        if ($stmt->execute()) {
            $course = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $course;
        } else {
            return NULL;
        }
    }
    
    public function checkAdmin($username, $password) {
        $stmt = $this->conn->prepare("SELECT * FROM admin WHERE username = ?");
        $stmt->bind_param("s", $username);
        if ($stmt->execute()) {
            $user = $stmt->get_result()->fetch_assoc();
            
            $hash = $this->checkhashSSHA($user["salt"], $password);
            if ($hash == $user["encrypted_pass"]) {
                return true;
            }
        }
        return false;
    }
    
    public function addPings($username, $crn, $date, $absent, $tardy, $listOfPings) {
        $stmt = $this->conn->prepare("INSERT INTO attendanceHistory(athlete, crn, date, absent, tardy, gps) VALUES(?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssiis", $username, $crn, $date, $absent, $tardy, $listOfPings);
        $result = $stmt->execute();
        $stmt->close();
 
        // check for successful store
        if ($result) {
            $stmt = $this->conn->prepare("SELECT * FROM attendanceHistory WHERE athlete = ? AND date = ? AND crn = ?");
            $stmt->bind_param("sss", $username, $date, $crn);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $user;
        } else {
            return false;
        }
    }
    
    public function getAthleteList() {
        $stmt = $this->conn->prepare("SELECT * FROM athletes");
        $result = $stmt->execute();
        if ($result) {
            $rows = array();
            
            foreach ($stmt->get_result()->fetch_all() as &$fetched) {
                $row = array();
                $row['name'] = $fetched[2];
                $row['sports'] = $fetched[6];
                $row['counselor'] = $fetched[8];
                $rows[] = $row;
            }
            return $rows;
        }
        return false;
    }
    
    public function adminApplyForRegister($email, $username, $password) {
        $stmt = $this->conn->prepare("SELECT * FROM admin_pending_registration WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        
        if ($stmt->execute()) {
            $stmt->fetch();
            if ($stmt->num_rows > 0) {
                $result = array();
                $result['success'] = false;
                $result['message'] = "Username or Email already registered.";
                $stmt->close();
                return $result;
            }
        }
        $stmt->close();
        
        $stmt = $this->conn->prepare("SELECT * FROM AthleteCheckIn.admin WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        
        if($stmt->execute()) {
            $stmt->fetch();
            if ($stmt->num_rows > 0) {
                $stmt->close();
                return array("success" => false, "message" => "Username or Email already exist.");
            }
        }
        $stmt->close();
        
        $hash = $this->hashSSHA($password);
        $stmt = $this->conn->prepare("INSERT INTO admin_pending_registration(username, encrypted_password, salt, email, registration_date) VALUES(?, ?, ?, ?, now())");
        $stmt->bind_param("ssss", $username, $hash["encrypted"], $hash["salt"], $email);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            return array("success" => true, "message" => "Successfully registered");
        } else {
            return array("success" => false, "message" => "Username or email already registered");
        }
    }
    
    public function getPendingAdminRegistrations() {
        $stmt = $this->conn->prepare("SELECT * FROM admin_pending_registration");
        $result = $stmt->execute();
        if ($result) {
            $rows = array();
            foreach ($stmt->get_result()->fetch_all() as &$fetched) {
                $row = array();
                $row['username'] = $fetched[1];
                $row['encrypted_pass'] = $fetched[2];
                $row['salt'] = $fetched[3];
                $row['email'] = $fetched[4];
                $row['register_date'] = $fetched[5];
                $rows[] = $row;
            }
            return $rows;
        }
    }
    
    public function removePendingAdmin($admin) {
        $stmt = $this->conn->prepare("DELETE FROM AthleteCheckIn.admin_pending_registration WHERE username = \"" . $admin["username"] . "\"");
        $stmt->execute();
        $stmt->close();
    }
    
    public function approveAdmin($admin) {
        $stmt = $this->conn->prepare("INSERT INTO AthleteCheckIn.admin(username, encrypted_pass, salt, email) VALUES(?, ?, ?, ?)");
        $user = $admin['username'];
        $pass = $admin['encrypted_pass'];
        $salt = $admin['salt'];
        $email = $admin['email'];
        $stmt->bind_param("ssss", $user, $pass, $salt, $email);
        $stmt->execute();
        $stmt->close();
        
        $stmt = $this->conn->prepare("DELETE FROM AthleteCheckIn.admin_pending_registration WHERE username = \"" . $admin["username"] . "\"");
        $stmt->execute();
        $stmt->close();
    }
}
 
?>