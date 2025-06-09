<?php
class RK_vraag
{
    private $conn;
    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function GetAllQuestionsByGameId($GameId)
    {
        $stmt = $this->conn->prepare("SELECT RK_vragen.Id as Id, Vraagtekst, Antwoord1, Antwoord2, Antwoord3, Antwoord4, GoedeAntwoord, AantalPunten, RK_KoppelCode.Code FROM RK_vragen INNER JOIN RK_KoppelCode ON RK_vragen.Id = RK_KoppelCode.VraagId WHERE RK_KoppelCode.SpelId = ?");

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

        // Haal het resultaat op als associatieve array
        $questions = [];
        while ($row = $result->fetch_assoc()) {
            $questions[] = $row;
        }

        return $questions;
    }

    public function GetQuestionByCode($QuestionCode, $spelId)
    {
        $stmt = $this->conn->prepare("SELECT * FROM RK_Vragen INNER JOIN RK_KoppelCode ON RK_vragen.Id = RK_KoppelCode.VraagId WHERE RK_KoppelCode.Code = ? AND RK_KoppelCode.SpelId = ?");

        if (!$stmt) {
            // Fout in voorbereiding van de query
            printf("Prepare failed: (%s) %s\n", $this->conn->errno, $this->conn->error);
            return null;
        }

        $stmt->bind_param("si", $QuestionCode, $spelId);

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

    public function GetQuestionById($QuestionId)
    {
        $stmt = $this->conn->prepare("SELECT * FROM RK_Vragen WHERE Id = ?");

        if (!$stmt) {
            // Fout in voorbereiding van de query
            printf("Prepare failed: (%s) %s\n", $this->conn->errno, $this->conn->error);
            return null;
        }

        $stmt->bind_param("i", $QuestionId);

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

    public function CreateQuestion($vraagtekst, $a1, $a2, $a3, $a4, $correct, $punten, $code, $spelId)
    {
        $stmt = $this->conn->prepare("INSERT INTO RK_vragen 
            (Vraagtekst, Antwoord1, Antwoord2, Antwoord3, Antwoord4, GoedeAntwoord, AantalPunten)
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        

        if (!$stmt) {
            // Fout in voorbereiding van de query
            printf("Prepare failed: (%s) %s\n", $this->conn->errno, $this->conn->error);
            return null;
        }

        $stmt->bind_param("ssssssi", $vraagtekst, $a1, $a2, $a3, $a4, $correct, $punten);

        if (!$stmt->execute()) {
            printf("Execute failed: (%s) %s\n", $stmt->errno, $stmt->error);
            return false;
        }

        $last_id = $this->conn->insert_id;
        $stmt = $this->conn->prepare("INSERT INTO RK_KoppelCode 
            (Code, VraagId, CasusId, SpelId)
            VALUES (?, ?, ?, ?)");
        

        if (!$stmt) {
            // Fout in voorbereiding van de query
            printf("Prepare failed: (%s) %s\n", $this->conn->errno, $this->conn->error);
            return null;
        }
        
        $casusId = -1;
        $stmt->bind_param("siii", $code, $last_id, $casusId, $spelId);

        if (!$stmt->execute()) {
            printf("Execute failed: (%s) %s\n", $stmt->errno, $stmt->error);
            return false;
        }
    
        return true;
    }

    public function UpdateQuestion($vraagtekst, $a1, $a2, $a3, $a4, $correct, $punten, $vraagId)
    {
        $stmt = $this->conn->prepare("UPDATE RK_vragen SET Vraagtekst=?, Antwoord1=?, Antwoord2=?, Antwoord3=?, Antwoord4=?, GoedeAntwoord=?, AantalPunten=? WHERE Id=?");
    
        if (!$stmt) {
            // Fout in voorbereiding van de query
            printf("Prepare failed: (%s) %s\n", $this->conn->errno, $this->conn->error);
            return null;
        }

        $stmt->bind_param("ssssssii", $vraagtekst, $a1, $a2, $a3, $a4, $correct, $punten, $vraagId);

        if (!$stmt->execute()) {
            printf("Execute failed: (%s) %s\n", $stmt->errno, $stmt->error);
            return false;
        }
    
        return true;
    }

    public function DeleteQuestion($QuestionId)
    {
        $stmt = $this->conn->prepare("DELETE FROM RK_vragen WHERE Id = ?");

        if (!$stmt) {
            // Fout in voorbereiding van de query
            printf("Prepare failed: (%s) %s\n", $this->conn->errno, $this->conn->error);
            return null;
        }

        $stmt->bind_param("i", $QuestionId);

        if (!$stmt->execute()) {
            printf("Execute failed: (%s) %s\n", $stmt->errno, $stmt->error);
            return false;
        }

        $stmt = $this->conn->prepare("DELETE FROM Rk_KoppelCode WHERE VraagId = ?");

        if (!$stmt) {
            // Fout in voorbereiding van de query
            printf("Prepare failed: (%s) %s\n", $this->conn->errno, $this->conn->error);
            return null;
        }

        $stmt->bind_param("i", $QuestionId);

        if (!$stmt->execute()) {
            printf("Execute failed: (%s) %s\n", $stmt->errno, $stmt->error);
            return false;
        }
    
        return true;
    }
}
?>