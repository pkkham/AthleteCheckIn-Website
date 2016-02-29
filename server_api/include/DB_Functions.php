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
        $stmt = $this->conn->prepare("SELECT * FROM admin WHERE user = ?");
        $stmt->bind_param("s", $username);
        if ($stmt->execute()) {
            $user = $stmt->get_result()->getch_assoc();
            
            $hash = $this->checkhashSSHA($user["salt"], $password);
            if ($hash == $user["encrypted_password"]) {
                return true;
            }
        }
        return false;
    }
}
 
?>