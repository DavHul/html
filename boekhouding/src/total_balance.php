<?php
class total_balance{
    private $servername = "localhost";
    private $username = "root";
    private $password = "usbw";
    private $dbname = "boekhouding_sc";

    function print_table(){
        $conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname);
        $sql = "SELECT * FROM total_stam";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
        // output data of each row
            while($row = $result->fetch_assoc()) {
                echo "id: " . $row["id"]. " - transaction_id: " . $row["transaction_id"]. " - direction: " . $row["direction"]. " - date: " . $row["date"].
                 " - amount: " . $row["amount"]. " - balance_before: " . $row["balance_before"]. " - balance_after: " . $row["balance_after"]. "<br>";
            }
        } else {
            echo "0 results";
        }
        $conn->close();
    }

    function print_html_table(){
        $conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname);
        $sql = "SELECT * FROM total_stam";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
        // output data of each row
            while($row = $result->fetch_assoc()) {
                echo "<tr><td>".$row["id"]."</td><td>" . $row["transaction_id"]. "</td><td>" . $row["direction"]. "</td><td>" . $row["date"]."</td>
                <td>&euro; " . $row["amount"]. " </td><td>&euro; " . $row["balance_before"]. "</td><td>&euro; " . $row["balance_after"]. "</td></tr>";
            }
        } else {
            echo "0 results";
        }
        $conn->close();
    }
    function print_balance(){
        $conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname);
        $sql = "SELECT max(id) as id FROM total_stam ";
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($result);
        $id = $row["id"];
        
        $sql = "SELECT balance_after FROM total_stam WHERE id=$id";
        $result = $conn->query($sql);

        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($result);
        $current_balance = $row["balance_after"];
        echo round($current_balance, 2);

        $conn->close();
    }

    function get_balances(){
        $balances = array();
        $conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname);
        $sql = "SELECT balance_after FROM total_stam";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
        // output data of each row
            while($row = $result->fetch_assoc()) {
                array_push($balances, $row["balance_after"]);
            }
        } else {
            echo "0 results";
        }
        $conn->close();
        return $balances;
    }

    function get_dates(){
        $dates = array();
        $conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname);
        $sql = "SELECT date FROM total_stam";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
        // output data of each row
            while($row = $result->fetch_assoc()) {
                array_push($dates, $row["date"]);
            }
        } else {
            echo "0 results";
        }
        $conn->close();
        return $dates;
    }

    function get_balance_split(){
        $output = array();
        $conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname);

        //rekening part
        $sql = "SELECT sum(amount) as rekening_income FROM income_stam WHERE category='rekening'";
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($result);
        $rekening_income = $row["rekening_income"];
        
        $sql = "SELECT sum(amount) as rekening_expense FROM expenses_stam WHERE category='rekening'";
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($result);
        $rekening_expense = $row["rekening_expense"];

        $rekening_total = $rekening_income - $rekening_expense;
        array_push($output, round($rekening_total, 2));

        //cash part
        $sql = "SELECT sum(amount) as cash_income FROM income_stam WHERE category='contant'";
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($result);
        $cash_income = $row["cash_income"];
        
        $sql = "SELECT sum(amount) as cash_expense FROM expenses_stam WHERE category='contant'";
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($result);
        $cash_expense = $row["cash_expense"];

        $cash_total = $cash_income - $cash_expense;
        array_push($output, round($cash_total, 2));

        $conn->close();
        return $output;
    }
}
/* $total_balance = new total_balance();
$q = $_REQUEST["q"];

if ($q == "print_table") {
    $total_balance->print_table();
} */