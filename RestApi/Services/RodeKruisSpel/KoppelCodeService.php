<?php
class RK_KoppelCode
{
    private $conn;
    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function GetDataByCodeAndSpelId($Code, $GameId)
    {
        $stmt = $this->conn->prepare("SELECT * FROM RK_KoppelCode WHERE Code = ? AND SpelId = ?");

        if (!$stmt) {
            // Fout in voorbereiding van de query
            printf("Prepare failed: (%s) %s\n", $this->conn->errno, $this->conn->error);
            return null;
        }

        $stmt->bind_param("si", $Code, $GameId);

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
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        return $data;
    }
}
?>