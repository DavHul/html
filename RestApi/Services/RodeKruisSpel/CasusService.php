<?php
class rk_Casus
{
    private $conn;
    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function GetAllCasussenByGameId($GameId)
    {
        $sql = "SELECT rk_casus.*, rk_koppelcode.Code 
                FROM rk_casus 
                INNER JOIN rk_koppelcode ON rk_casus.Id = rk_koppelcode.CasusId 
                WHERE rk_koppelcode.SpelId = ?";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            printf("Prepare failed: (%s) %s\n", $this->conn->errno, $this->conn->error);
            return null;
        }

        $stmt->bind_param("i", $GameId);
        $stmt->execute();

        $result = $stmt->get_result();

        if (!$result) {
            printf("Query failed: (%s) %s\n", $this->conn->errno, $this->conn->error);
            return null;
        }

        $casussen = [];
        while ($row = $result->fetch_assoc()) {
            $casussen[] = $row;
        }

        return $casussen;
    }

    public function GetCasusByCode($CasusCode)
    {
        $sql = "SELECT rk_casus.*, rk_koppelcode.Code 
                FROM rk_casus 
                INNER JOIN rk_koppelcode ON rk_casus.Id = rk_koppelcode.CasusId 
                WHERE rk_koppelcode.Code = ?";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            printf("Prepare failed: (%s) %s\n", $this->conn->errno, $this->conn->error);
            return null;
        }

        $stmt->bind_param("s", $CasusCode);
        $stmt->execute();

        $result = $stmt->get_result();

        if (!$result) {
            printf("Query failed: (%s) %s\n", $this->conn->errno, $this->conn->error);
            return null;
        }

        return $result->fetch_assoc();
    }

    public function GetCasusById($Id)
    {
        $sql = "SELECT * 
                FROM rk_casus 
                WHERE Id = ?";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            printf("Prepare failed: (%s) %s\n", $this->conn->errno, $this->conn->error);
            return null;
        }

        $stmt->bind_param("i", $Id);
        $stmt->execute();

        $result = $stmt->get_result();

        if (!$result) {
            printf("Query failed: (%s) %s\n", $this->conn->errno, $this->conn->error);
            return null;
        }

        return $result->fetch_assoc();
    }

    public function CreateCasus($values, $gameId, $code) {
        $stmt = $this->conn->prepare("INSERT INTO rk_casus (
            Naam, MaximaalPunten, InformatieAnder, InformatieZelf, NaamSlachtoffer, GeboorteDatum,
            Geslacht, Woonplaats, Toedracht, LuchtwegStatus, AdemhalingFrequentie, AdemhalingSymetrie,
            HartslagFrequentie, HartslagRitme, HartslagKracht, BewustzijnScore, Temperatuur,
            Opmerkingen, Allergie, Medicatie, ZiekteGeschiedenis, Letsel, Omstandigheden
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        if (!$stmt) {
            printf("Prepare failed (casus): (%s) %s\n", $this->conn->errno, $this->conn->error);
            return null;
        }

        // Typen bepalen (allemaal strings of passend per veld)
        $types = "sissssssssisisssdssssss"; 
        $stmt->bind_param(
            $types,
            $values['Naam'], $values['MaximaalPunten'], $values['InformatieAnder'], $values['InformatieZelf'],
            $values['NaamSlachtoffer'], $values['GeboorteDatum'], $values['Geslacht'], $values['Woonplaats'],
            $values['Toedracht'], $values['LuchtwegStatus'], $values['AdemhalingFrequentie'], $values['AdemhalingSymetrie'],
            $values['HartslagFrequentie'], $values['HartslagRitme'], $values['HartslagKracht'], $values['BewustzijnScore'],
            $values['Temperatuur'], $values['Opmerkingen'], $values['Allergie'], $values['Medicatie'],
            $values['ZiekteGeschiedenis'], $values['Letsel'], $values['Omstandigheden']
        );

        if (!$stmt->execute()) {
            printf("Execute failed: (%s) %s\n", $stmt->errno, $stmt->error);
            return false;
        }

        //In koppelcode tabel zetten
        $last_id = $this->conn->insert_id;
        $stmt = $this->conn->prepare("INSERT INTO rk_koppelcode 
            (Code, VraagId, CasusId, SpelId)
            VALUES (?, ?, ?, ?)");
        

        if (!$stmt) {
            // Fout in voorbereiding van de query
            printf("Prepare failed: (%s) %s\n", $this->conn->errno, $this->conn->error);
            return null;
        }
        
        $vraagId = -1;
        $stmt->bind_param("siii", $code, $vraagId, $last_id, $gameId);

        if (!$stmt->execute()) {
            printf("Execute failed: (%s) %s\n", $stmt->errno, $stmt->error);
            return false;
        }

        return true;
    }

    public function UpdateCasus($values, $casusId) {
        $stmt = $this->conn->prepare("UPDATE rk_casus SET
            Naam = ?, MaximaalPunten = ?, InformatieAnder = ?, InformatieZelf = ?, NaamSlachtoffer = ?, GeboorteDatum = ?,
            Geslacht = ?, Woonplaats = ?, Toedracht = ?, LuchtwegStatus = ?, AdemhalingFrequentie = ?, AdemhalingSymetrie = ?,
            HartslagFrequentie = ?, HartslagRitme = ?, HartslagKracht = ?, BewustzijnScore = ?, Temperatuur = ?,
            Opmerkingen = ?, Allergie = ?, Medicatie = ?, ZiekteGeschiedenis = ?, Letsel = ?, Omstandigheden = ?
            WHERE Id = ?");

        if (!$stmt) {
            printf("Prepare failed (spel): (%s) %s\n", $this->conn->errno, $this->conn->error);
            return null;
        }

        $types = "sissssssssisisssdssssss" . "i"; // 23 velden + ID
        $stmt->bind_param(
            $types,
            $values['Naam'], $values['MaximaalPunten'], $values['InformatieAnder'], $values['InformatieZelf'],
            $values['NaamSlachtoffer'], $values['GeboorteDatum'], $values['Geslacht'], $values['Woonplaats'],
            $values['Toedracht'], $values['LuchtwegStatus'], $values['AdemhalingFrequentie'], $values['AdemhalingSymetrie'],
            $values['HartslagFrequentie'], $values['HartslagRitme'], $values['HartslagKracht'], $values['BewustzijnScore'],
            $values['Temperatuur'], $values['Opmerkingen'], $values['Allergie'], $values['Medicatie'],
            $values['ZiekteGeschiedenis'], $values['Letsel'], $values['Omstandigheden'], $casusId
        );

         if (!$stmt->execute()) {
            printf("Execute failed: (%s) %s\n", $stmt->errno, $stmt->error);
            return false;
        }
        return true;
    }

    public function DeleteCasus($CasusId)
    {
        $stmt = $this->conn->prepare("DELETE FROM rk_casus WHERE Id = ?");

        if (!$stmt) {
            printf("Prepare failed: (%s) %s\n", $this->conn->errno, $this->conn->error);
            return null;
        }

        $stmt->bind_param("i", $CasusId);

        if (!$stmt->execute()) {
            printf("Execute failed: (%s) %s\n", $stmt->errno, $stmt->error);
            return false;
        }

        $stmt = $this->conn->prepare("DELETE FROM rk_koppelcode WHERE CasusId = ?");

        if (!$stmt) {
            printf("Prepare failed: (%s) %s\n", $this->conn->errno, $this->conn->error);
            return null;
        }

        $stmt->bind_param("i", $CasusId);

        if (!$stmt->execute()) {
            printf("Execute failed: (%s) %s\n", $stmt->errno, $stmt->error);
            return false;
        }

        return true;
    }
}
?>