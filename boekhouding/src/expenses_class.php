<?php
class expenses_class{
    private $servername;
    private $username;
    private $password;
    private $dbname = "boekhouding_sc";

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
        $sql = "SELECT * FROM expenses_stam";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
        // output data of each row
            while($row = $result->fetch_assoc()) {
                echo "id: " . $row["id"]. " - date: " . $row["date"]. " - title: " . $row["title"]. " - description: " . $row["description"].
                 " - amount: " . $row["amount"]. " - target: " . $row["target"]. " - category: " . $row["category"]. " - finished: " . $row["finished"]. "<br>";
            }
        } else {
            echo "0 results";
        }
        $conn->close();
    }

    function print_html_table(){
        $conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname);
        $sql = "SELECT * FROM expenses_stam";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
        // output data of each row
            while($row = $result->fetch_assoc()) {
                echo "<tr><td>".$row["id"]."</td><td>" . $row["date"]. "</td><td>" . $row["title"]. "</td><td>" . $row["description"]."</td>
                <td>&euro; " . $row["amount"]. " </td><td>" . $row["target"]. "</td><td>" . $row["category"]. "</td><td>" . $row["finished"]. "</td><td><form name='add_expense' method='post' action='#'><input type='hidden' id=id_number name=id_number value=".$row["id"]."><input type='submit' value='Aanpassen' name='change_expense'></form></td></tr>";
            }
        } 
        /* else {
            echo "0 results";
        } */
        $conn->close();
    }

    function get_data($id_number){
        $conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname);
        $sql = "SELECT * FROM expenses_stam WHERE id=$id_number";
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($result);
        $conn->close();
        return [$row["id"], $row["date"], $row["title"], $row["description"], $row["amount"], $row["target"], $row["category"], $row["finished"]];
    }

    function change_expense($id_number, $date, $title, $description, $amount, $target, $category, $complete){
        $conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname);
        $sql = "UPDATE expenses_stam SET date='$date', title='$title', description='$description', amount=$amount, target='$target', category='$category', finished='$complete'
        WHERE id = $id_number";
        if ($conn->query($sql) === TRUE) {
            echo "Record updated successfully";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
        $conn->close();
    }

    function total_out(){
        $conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname);
        $sql = "SELECT sum(amount) as total_amount FROM expenses_stam ";
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($result);
        echo round($row["total_amount"],2);
        
        $conn->close();
    }

    function add_expense($date, $title, $description, $amount, $target, $category, $complete){
        $conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname);
        $sql = "INSERT INTO expenses_stam (date, title, description, amount, target, category, finished) 
        VALUES ('$date', '$title', '$description', $amount, '$target', '$category', '$complete')";
        if ($conn->query($sql) === TRUE) {
            echo "New record created successfully";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
        $sql = "SELECT max(id) as id FROM expenses_stam ";
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($result);
        $transaction_id = $row["id"];

        //total_stam table
        $sql = "SELECT max(id) as id FROM total_stam ";
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($result);
        $id = $row["id"];

        $sql = "SELECT balance_after FROM total_stam WHERE id=$id";
        $result = $conn->query($sql);

        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($result);
        $last_balance = $row["balance_after"];
        $new_balance = $last_balance - $amount;
        $amount = $amount * -1;
        $sql = "INSERT INTO total_stam (transaction_id, direction, date, amount, balance_before, balance_after) 
        VALUES ($transaction_id, 'expense', '$date', $amount, '$last_balance', '$new_balance')";
        if ($conn->query($sql) === TRUE) {
            echo "New record created successfully";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
        $conn->close();
    }
    function get_last_id(){
        $conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname);
        $sql = "SELECT max(id) as id FROM expenses_stam ";
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($result);
        $last_id = $row["id"];
        $conn->close();
        return $last_id;
    }
}
/* $total_balance = new total_balance();
$q = $_REQUEST["q"];

if ($q == "print_table") {
    $total_balance->print_table();
} */
