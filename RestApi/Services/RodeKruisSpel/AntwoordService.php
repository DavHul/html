<?php
require_once '../RestApi/Services/RodeKruisSpel/VraagService.php';
class RK_antwoord
{
    private $conn;
    private $questionObj;

    public function __construct($conn)
    {
        $this->conn = $conn;
        $this->questionObj = new RK_vraag($conn);
    }

    public function SubmitAnswer($QuestionId, $TeamId, $GivenAnswer){
        //Vraag ophalen en resultaat bepalen
        $question = $this->questionObj->GetQuestionById($QuestionId);

        $AantalPuntenVerdiend = 0;
        $Correct = 0;
        if ($question["GoedeAntwoord"] == $GivenAnswer){
            $AantalPuntenVerdiend = $question["AantalPunten"];
            $Correct = 1;
        }

        //Checken of al eerder beantwoord door in tabel te kijken op question id en team id
        $previousAnswer = $this->GetAnswerByQuestionIdAndTeamId($QuestionId, $TeamId);
        if ($previousAnswer){
            $stmt = $this->conn->prepare("UPDATE RK_Antwoorden SET GegevenAntwoord=?, AntwoordCorrect = ?, PuntenVerdiend = ? WHERE Id=?");
    
            if (!$stmt) {
                // Fout in voorbereiding van de query
                printf("Prepare failed: (%s) %s\n", $this->conn->errno, $this->conn->error);
                return null;
            }

            $stmt->bind_param("siii", $GivenAnswer, $Correct, $AantalPuntenVerdiend, $previousAnswer["Id"]);

            if (!$stmt->execute()) {
                printf("Execute failed: (%s) %s\n", $stmt->errno, $stmt->error);
                return false;
            }
        
            return true;
        }else{
            $stmt = $this->conn->prepare("INSERT INTO RK_Antwoorden
            (VraagId, TeamId, GegevenAntwoord, AntwoordCorrect, PuntenVerdiend)
            VALUES (?, ?, ?, ?, ?)");
        

            if (!$stmt) {
                // Fout in voorbereiding van de query
                printf("Prepare failed: (%s) %s\n", $this->conn->errno, $this->conn->error);
                return null;
            }

            $stmt->bind_param("iisii", $QuestionId, $TeamId, $GivenAnswer, $Correct, $AantalPuntenVerdiend);

            if (!$stmt->execute()) {
                printf("Execute failed: (%s) %s\n", $stmt->errno, $stmt->error);
                return false;
            }
        
            return true;
        }
    }

    public function GetAnswerByQuestionIdAndTeamId($QuestionId, $TeamId)
    {
        $stmt = $this->conn->prepare("SELECT * FROM RK_Antwoorden WHERE VraagId = ? AND TeamId = ?");

        if (!$stmt) {
            // Fout in voorbereiding van de query
            printf("Prepare failed: (%s) %s\n", $this->conn->errno, $this->conn->error);
            return null;
        }

        $stmt->bind_param("ii", $QuestionId, $TeamId);

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

    public function DeleteAnswer($QuestionId)
    {
        $stmt = $this->conn->prepare("DELETE FROM RK_Antwoorden WHERE Id = ?");

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

    public function GetAllAnswersByTeamId($TeamId)
    {
        $stmt = $this->conn->prepare("SELECT * FROM RK_Antwoorden WHERE TeamId = ?");
        $stmt->bind_param("i", $TeamId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
?>