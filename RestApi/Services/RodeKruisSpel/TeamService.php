<?php
class RK_team
{
    private $conn;
    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function GetAllTeamsByGameId($SpelId)
    {
        $stmt = $this->conn->prepare("SELECT * FROM rk_team WHERE SpelId = ?");

        if (!$stmt) {
            // Fout in voorbereiding van de query
            printf("Prepare failed: (%s) %s\n", $this->conn->errno, $this->conn->error);
            return null;
        }

        $stmt->bind_param("i", $SpelId);

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

    public function GetTeamByCredentials($SpelCode, $TeamWachtwoord)
    {
        $stmt = $this->conn->prepare("SELECT Id FROM rk_spel WHERE Code = ?");
        if (!$stmt) {
            printf("Prepare failed (spel): (%s) %s\n", $this->conn->errno, $this->conn->error);
            return null;
        }

        $stmt->bind_param("s", $SpelCode);
        $stmt->execute();
        $result = $stmt->get_result();

        if (!$result || $result->num_rows === 0) {
            return null; // Geen spel gevonden
        }

        $spel = $result->fetch_assoc();
        $spelId = $spel['Id'];

        // Stap 2: Zoek team met dat spelId en wachtwoord
        $stmt2 = $this->conn->prepare("SELECT * FROM rk_team WHERE SpelId = ? AND Wachtwoord = ?");
        if (!$stmt2) {
            printf("Prepare failed (team): (%s) %s\n", $this->conn->errno, $this->conn->error);
            return null;
        }

        $stmt2->bind_param("is", $spelId, $TeamWachtwoord);
        $stmt2->execute();
        $result2 = $stmt2->get_result();

        if (!$result2 || $result2->num_rows === 0) {
            return null; // Geen team gevonden
        }

        return $result2->fetch_assoc();
    }

    public function CreateTeam($Name, $Password, $GameId)
    {
        $stmt = $this->conn->prepare("INSERT INTO rk_team (Naam, Wachtwoord, SpelId) VALUES (?, ?, ?)");

        if (!$stmt) {
            // Fout in voorbereiding van de query
            printf("Prepare failed: (%s) %s\n", $this->conn->errno, $this->conn->error);
            return null;
        }

        $stmt->bind_param("ssi", $Name, $Password, $GameId);

        if (!$stmt->execute()) {
            printf("Execute failed: (%s) %s\n", $stmt->errno, $stmt->error);
            return false;
        }
    
        return true;
    }

    public function DeleteTeam($TeamId)
    {
        $stmt = $this->conn->prepare("DELETE FROM rk_team WHERE Id = ?");

        if (!$stmt) {
            // Fout in voorbereiding van de query
            printf("Prepare failed: (%s) %s\n", $this->conn->errno, $this->conn->error);
            return null;
        }

        $stmt->bind_param("i", $TeamId);

        if (!$stmt->execute()) {
            printf("Execute failed: (%s) %s\n", $stmt->errno, $stmt->error);
            return false;
        }
    
        return true;
    }
}
?>