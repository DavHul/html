<?php
class rk_vraag
{
    private $conn;
    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function GetAllQuestionsByGameId($GameId)
    {
        $stmt = $this->conn->prepare("SELECT rk_vragen.Id as Id, Vraagtekst, Antwoord1, Antwoord2, Antwoord3, Antwoord4, GoedeAntwoord, AantalPunten, rk_koppelcode.Code FROM rk_vragen INNER JOIN rk_koppelcode ON rk_vragen.Id = rk_koppelcode.VraagId WHERE rk_koppelcode.SpelId = ?");

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
        $stmt = $this->conn->prepare("SELECT * FROM rk_vragen INNER JOIN rk_koppelcode ON rk_vragen.Id = rk_koppelcode.VraagId WHERE rk_koppelcode.Code = ? AND rk_koppelcode.SpelId = ?");

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
        $stmt = $this->conn->prepare("SELECT * FROM rk_vragen WHERE Id = ?");

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
        $stmt = $this->conn->prepare("INSERT INTO rk_vragen 
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
        $stmt = $this->conn->prepare("INSERT INTO rk_koppelcode 
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
        $stmt = $this->conn->prepare("UPDATE rk_vragen SET Vraagtekst=?, Antwoord1=?, Antwoord2=?, Antwoord3=?, Antwoord4=?, GoedeAntwoord=?, AantalPunten=? WHERE Id=?");
    
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
        $stmt = $this->conn->prepare("DELETE FROM rk_vragen WHERE Id = ?");

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

        $stmt = $this->conn->prepare("DELETE FROM rk_koppelcode WHERE VraagId = ?");

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