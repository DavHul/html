<?php
class AdminUsers
{
    private $conn;
    public function __construct($conn)
    {
        $this->conn = $conn;
    }
    public function CheckCredentials($Username, $Password)
    {
        $stmt = $this->conn->prepare("SELECT * FROM adminusers WHERE BINARY Username = ? AND BINARY Password = ?");

        if (!$stmt) {
            // Fout in voorbereiding van de query
            printf("Prepare failed: (%s) %s\n", $this->conn->errno, $this->conn->error);
            return null;
        }

        // Bind de parameters aan de query (2 strings: "ss")
        $stmt->bind_param("ss", $Username, $Password);

        // Voer de query uit
        $stmt->execute();

        // Haal het resultaat op
        $result = $stmt->get_result();

        if (!$result) {
            // Fout in ophalen resultaat
            printf("Query failed: (%s) %s\n", $this->conn->errno, $this->conn->error);
            return null;
        }

        // Haal het resultaat op als associatieve array
        $adminUser = $result->fetch_assoc();

        if ($adminUser) {
            return $adminUser;
        } else {
            return null;
        }
    }

    public function CheckGuid($Guid)
    {
        $stmt = $this->conn->prepare("SELECT * FROM adminusers WHERE BINARY Guid = ?");

        if (!$stmt) {
            // Fout in voorbereiding van de query
            printf("Prepare failed: (%s) %s\n", $this->conn->errno, $this->conn->error);
            return null;
        }

        // Bind de parameters aan de query (2 strings: "ss")
        $stmt->bind_param("s", $Guid);

        // Voer de query uit
        $stmt->execute();

        // Haal het resultaat op
        $result = $stmt->get_result();

        if (!$result) {
            // Fout in ophalen resultaat
            printf("Query failed: (%s) %s\n", $this->conn->errno, $this->conn->error);
            return null;
        }

        // Haal het resultaat op als associatieve array
        $adminUser = $result->fetch_assoc();

        if ($adminUser) {
            return $adminUser;
        } else {
            return null;
        }
    }
}
?>