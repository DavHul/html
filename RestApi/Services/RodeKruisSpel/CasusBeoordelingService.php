<?php
class rk_casusbeoordeling
{
    private $conn;
    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function GetBeoordelingByTeamAndCasusId($TeamId, $CasusId)
    {
        $stmt = $this->conn->prepare("SELECT * FROM rk_casusbeoordeling WHERE TeamId = ? AND CasusId = ?");

        if (!$stmt) {
            // Fout in voorbereiding van de query
            printf("Prepare failed: (%s) %s\n", $this->conn->errno, $this->conn->error);
            return null;
        }
        $stmt->bind_param("ii", $TeamId, $CasusId);
        $stmt->execute();
        $result = $stmt->get_result();

        if (!$result) {
            printf("Query failed: (%s) %s\n", $this->conn->errno, $this->conn->error);
            return null;
        }

        return $result->fetch_assoc();
    }

    public function CreateBeoordeling($CasusId, $TeamId, $PuntenCentralist, $PuntenSlachtoffer)
    {
        $stmt = $this->conn->prepare("INSERT INTO rk_casusbeoordeling (CasusId, TeamId, PuntenCentralist, PuntenSlachtoffer) VALUES (?, ?, ?, ?)");

        if (!$stmt) {
            // Fout in voorbereiding van de query
            printf("Prepare failed: (%s) %s\n", $this->conn->errno, $this->conn->error);
            return null;
        }

        $stmt->bind_param("iiii", $CasusId, $TeamId, $PuntenCentralist, $PuntenSlachtoffer);

        if (!$stmt->execute()) {
            printf("Execute failed: (%s) %s\n", $stmt->errno, $stmt->error);
            return false;
        }
    
        return true;
    }

    public function UpdateBeoordeling($BeoordelingId, $CasusId, $TeamId, $PuntenCentralist, $PuntenSlachtoffer)
    {
       $stmt = $this->conn->prepare("UPDATE rk_casusbeoordeling SET CasusId = ?, TeamId = ?, PuntenCentralist = ?, PuntenSlachtoffer = ? WHERE id = ?");
        
        if (!$stmt) {
            // Fout in voorbereiding van de query
            printf("Prepare failed: (%s) %s\n", $this->conn->errno, $this->conn->error);
            return null;
        }

        $stmt->bind_param("iiiii", $CasusId, $TeamId, $PuntenCentralist, $PuntenSlachtoffer, $BeoordelingId);

        if (!$stmt->execute()) {
            printf("Execute failed: (%s) %s\n", $stmt->errno, $stmt->error);
            return false;
        }
    
        return true;
    }

    public function GetAllBeoordelingenByTeamId($TeamId)
    {
        $stmt = $this->conn->prepare("SELECT * FROM rk_casusbeoordeling WHERE TeamId = ?");
        $stmt->bind_param("i", $TeamId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

}
?>