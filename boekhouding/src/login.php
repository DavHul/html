<?php
class login{
    private $servername;
    private $username;
    private $password;
    private $dbname = "boekhouding_sc";
    private $value;

    function get_db_credentials(){
        // Read the JSON file  
        $json = file_get_contents(__DIR__."/../../credentials.json"); 
        
        // Decode the JSON file 
        $json_data = json_decode($json,true); 
        
        // Display data 
        $this->servername = $json_data["data"][0]["servername"]; 
        $this->username = $json_data["data"][0]["username"]; 
        $this->password = $json_data["data"][0]["password"]; 
    }

    function print_table(){
        $conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname);
        $sql = "SELECT * FROM credentials";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
        // output data of each row
        //print_r($result->fetch_assoc());
            while($row = $result->fetch_assoc()) {
                echo "id: " . $row["id"]. " - username: " . $row["username"]. " - password: " . $row["password"]. " - role: " . $row["role"]. "<br>";
            }
        } else {
            echo "0 results";
        }
        $conn->close();
    }

    function check_login(){
        if(!isset($_COOKIE["ingelogd"])) {
           return "false";
        } else {
            $value = explode('-', $_COOKIE["ingelogd"]);
            return "$value[0]";
        }
    }

    function log_in($username, $password){
        $conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname);
        $sql = "SELECT * FROM credentials WHERE username='$username' and password='$password'";
        $result = mysqli_query($conn, $sql);
                  
        if ($result->num_rows > 0) {
            // output data of each row
            $row = $result->fetch_assoc();
            $this->value = $row["role"]."-". $row["id"];
            setcookie("ingelogd", $this->value, time() + (86400 * 30), "/");
            return "true";
          }else {
            return "false";
          }
        $conn->close();
    }

    function log_out(){
        setcookie("ingelogd", $this->value, time()-3600, "/");
        $this->check_login();
    }
}
date_default_timezone_set("Europe/Amsterdam");
/* $login_class = new login();
$q = $_REQUEST["q"];

if ($q == "print_table") {
    $login_class->print_table();
}elseif ($q == "check_login") {
    $login_class->check_login();
}elseif (!(strpos($q, "login"))){
    $data = explode(';', $q); //login;username;password
    $login_class->log_in($data[1], $data[2]);
} */
?>
