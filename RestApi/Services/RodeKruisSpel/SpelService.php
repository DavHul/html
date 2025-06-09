<?php
class RK_spel
{
    private $conn;
    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function GetAllGamesByAdminId($AdminId)
    {
        $stmt = $this->conn->prepare("SELECT * FROM rk_spel INNER JOIN rk_koppelcode ON rk_spel.ID = rk_koppelcode.VraagId WHERE AdminId = ?");

        if (!$stmt) {
            // Fout in voorbereiding van de query
            printf("Prepare failed: (%s) %s\n", $this->conn->errno, $this->conn->error);
            return null;
        }

        $stmt->bind_param("i", $AdminId);

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
        $games = [];
        while ($row = $result->fetch_assoc()) {
            $games[] = $row;
        }

        return $games;
    }

    public function GetGameById($GameId)
    {
        $stmt = $this->conn->prepare("SELECT * FROM rk_spel WHERE Id = ?");

        if (!$stmt) {
            // Fout in voorbereiding van de query
            printf("Prepare failed: (%s) %s\n", $this->conn->errno, $this->conn->error);
            return null;
        }

        $stmt->bind_param("i", $GameId);

        // Voer de query uit
        $stmt->execute();

        // Haal het resultaat op
        $result = $stmt->get_result();

        if (!$result) {
            // Fout in ophalen resultaat
            printf("Query failed: (%s) %s\n", $this->conn->errno, $this->conn->error);
            return null;
        }

        return $result->fetch_assoc();
    }

    public function CreateGame($Name, $AdminId, $Code)
    {
        $stmt = $this->conn->prepare("INSERT INTO rk_spel (Naam, AdminId, Code) VALUES (?, ?, ?)");

        if (!$stmt) {
            // Fout in voorbereiding van de query
            printf("Prepare failed: (%s) %s\n", $this->conn->errno, $this->conn->error);
            return null;
        }

        $stmt->bind_param("sis", $Name, $AdminId, $Code);

        if (!$stmt->execute()) {
            printf("Execute failed: (%s) %s\n", $stmt->errno, $stmt->error);
            return false;
        }
    
        return true;
    }

    public function UpdateGame($Name, $Code, $GameId)
    {
        $stmt = $this->conn->prepare("UPDATE rk_spel SET naam = ?, code = ? WHERE id = ?");
        
        if (!$stmt) {
            // Fout in voorbereiding van de query
            printf("Prepare failed: (%s) %s\n", $this->conn->errno, $this->conn->error);
            return null;
        }

        $stmt->bind_param("ssi", $Name, $Code, $GameId);

        if (!$stmt->execute()) {
            printf("Execute failed: (%s) %s\n", $stmt->errno, $stmt->error);
            return false;
        }
    
        return true;
    }
}
?>
