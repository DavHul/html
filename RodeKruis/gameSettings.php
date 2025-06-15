<?php
session_start();
require_once '../RestApi/config.php';
require_once '../RestApi/Services/AdminUserService.php';
require_once '../RestApi/Services/RodeKruisSpel/SpelService.php';
require_once '../RestApi/Services/RodeKruisSpel/TeamService.php';
require_once '../RestApi/Services/RodeKruisSpel/VraagService.php';
require_once '../RestApi/Services/RodeKruisSpel/CasusService.php';

$spelObj = new RK_spel($conn);
$teamObj = new RK_team($conn);
$questionObj = new RK_vraag($conn);
$casusObj = new RK_Casus($conn);
$adminUserObj = new AdminUsers($conn);

$GameId = $_SESSION['selected_game_id'];
if (!$GameId) {
    die("Geen spel geselecteerd.");
}

$success = '';
$error = '';

// Spel bijwerken
if ($_SERVER['REQUEST_METHOD'] === 'POST'&& isset($_POST['code'])) {
    $naam = $_POST['naam'] ?? '';
    $code = $_POST['code'] ?? '';

    if ($naam && $code) {
        if ($spelObj->UpdateGame($naam, $code, $GameId)) {
            $success = "Spel updaten succesvol!";
        } else {
            $error = "Spel updaten fout gegaan!";
        }
    } else {
        $error = "Vul alle velden in.";
    }
}

$spel = $spelObj->GetGameById($GameId);
if (!$spel) {
    die("Spel niet gevonden.");
}

//-------------------teams-------------------
$teamSuccess = '';
$teamError = '';

//Team maken
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_team'])) {
    $teamNaam = $_POST['team_naam'] ?? '';
    $teamWachtwoord = $_POST['team_wachtwoord'] ?? '';

    if ($teamNaam && $teamWachtwoord) {
        if ($teamObj->CreateTeam($teamNaam, $teamWachtwoord, $GameId)) {
            $teamSuccess = "Team toegevoegd.";
        } else {
            $teamError = "Fout bij toevoegen team.";
        }
    } else {
        $teamError = "Vul alle velden in voor het team.";
    }
}

//Team verwijderen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_team_id'])) {
    $teamIdToDelete = $_POST['delete_team_id'];

    if ($teamObj->DeleteTeam($teamIdToDelete)) {
        $teamSuccess = "Team verwijderd.";
    } else {
        $teamError = "Fout bij verwijderen team: " . $stmt->error;
    }
}

// Teams ophalen
$teams = $teamObj->GetAllTeamsByGameId($GameId);

//----------------vragen----------------------
$vraagSuccess = '';
$vraagError = '';

// Vraag toevoegen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_question'])) {
    $vraagtekst = $_POST['vraagtekst'] ?? '';
    $a1 = $_POST['antwoord1'] ?? '';
    $a2 = $_POST['antwoord2'] ?? '';
    $a3 = $_POST['antwoord3'] ?? '';
    $a4 = $_POST['antwoord4'] ?? '';
    $correct = $_POST['correct_antwoord'] ?? '';
    $punten = intval($_POST['punten'] ?? 0);
    $code = $_POST['vraagcode'] ?? '';

    if ($vraagtekst && $a1 && $a2 && $a3 && $a4 && $correct) {
        if ($questionObj->CreateQuestion($vraagtekst, $a1, $a2, $a3, $a4, $correct, $punten, $code, $GameId)) {
            $vraagSuccess = "Vraag toegevoegd.";
        } else {
            $vraagError = "Fout bij toevoegen vraag.";
        }
    } else {
        $vraagError = "Alle velden zijn verplicht.";
    }
}

// Vraag verwijderen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_question_id'])) {
    $vraagId = $_POST['delete_question_id'];

    if ($questionObj->DeleteQuestion($vraagId)) {
        $vraagSuccess = "Vraag verwijderd.";
    } else {
        $vraagError = "Fout bij verwijderen vraag: " . $stmt->error;
    }
}
// Bewerken van een vraag
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_question'])) {
    $vraagId = intval($_POST['vraag_id']);
    $vraagtekst = $_POST['vraagtekst'];
    $a1 = $_POST['antwoord1'];
    $a2 = $_POST['antwoord2'];
    $a3 = $_POST['antwoord3'];
    $a4 = $_POST['antwoord4'];
    $correct = $_POST['correct_antwoord'];
    $punten = intval($_POST['punten']);
    $code = $_POST['vraagcode'];

    if ($questionObj->UpdateQuestion($vraagtekst, $a1, $a2, $a3, $a4, $correct, $punten, $vraagId)) {
        $vraagSuccess = "Vraag bijgewerkt.";
    } else {
        $vraagError = "Fout bij bijwerken vraag: " . $stmt->error;
    }
}

$editVraagId = -1;
// Welke vraag wordt bewerkt?
if (isset($_POST['edit_question_id'])){
    $editVraagId = $_POST['edit_question_id'];
}


// Haal alle vragen op voor dit spel
$vragen = $questionObj->GetAllQuestionsByGameId($GameId);

//-----------------------Casussen-----------------------------
$casusSuccess = '';
$casusError = '';

// Kolomnamen hardcoded
$columns = ['Naam', 'MaximaalPunten', 'InformatieAnder', 'InformatieZelf', 'NaamSlachtoffer', 'GeboorteDatum',
    'Geslacht', 'Woonplaats', 'Toedracht', 'LuchtwegStatus', 'AdemhalingFrequentie', 'AdemhalingSymetrie',
    'HartslagFrequentie', 'HartslagRitme', 'HartslagKracht', 'BewustzijnScore', 'Temperatuur', 'Opmerkingen',
    'Allergie', 'Medicatie', 'ZiekteGeschiedenis', 'Letsel', 'Omstandigheden'];

// Toevoegen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_casus'])) {
    $values = [];
    foreach ($columns as $col) {
        $values[$col] = $_POST[$col] ?? '';
    }

    $code = $_POST['Code'] ?? '';

    if (!empty($values['Naam'])) {
        if ($casusObj->CreateCasus($values, $GameId, $code)) {
            $casusSuccess = "Casus toegevoegd.";
        } else {
            $casusError = "Fout bij toevoegen casus.";
        }
    } else {
        $casusError = "Naam is verplicht.";
    }
}

// Verwijderen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_casus_id'])) {
    if ($casusObj->DeleteCasus(intval($_POST['delete_casus_id']))) {
        $casusSuccess = "Casus verwijderd.";
    } else {
        $casusError = "Fout bij verwijderen casus.";
    }
}

// Bewerken
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_casus'])) {
    $casusId = intval($_POST['casus_id']);
    $updated = [];
    foreach ($columns as $col) {
        $updated[$col] = $_POST[$col];
    }

    if ($casusObj->UpdateCasus($updated, $casusId)) {
        $casusSuccess = "Casus bijgewerkt.";
    } else {
        $casusError = "Fout bij bewerken casus.";
    }
}

$editCasusId = -1;
// Welke casus wordt bewerkt?
if (isset($_POST['edit_casus_id'])){
    $editCasusId = $_POST['edit_casus_id'];
}
$casussen = $casusObj->GetAllCasussenByGameId($GameId);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Spel Instellen</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Spel Instellen: <?= htmlspecialchars($spel['Naam']) ?></h1>
        <br><a href="dashboard.php">Terug naar overzicht</a>

        <?php if ($success): ?><p style="color:green;"><?= $success ?></p><?php endif; ?>
        <?php if ($error): ?><p style="color:red;"><?= $error ?></p><?php endif; ?>

        <form method="post">
            <label>Naam:</label><br>
            <input type="text" name="naam" value="<?= htmlspecialchars($spel['Naam']) ?>"><br><br>

            <label>Code:</label><br>
            <input type="text" name="code" value="<?= htmlspecialchars($spel['Code']) ?>"><br><br>

            <button type="submit">Opslaan</button>
        </form>

        <h2>Teams</h2>
        <?php if ($teamSuccess): ?><p style="color:green;"><?= $teamSuccess ?></p><?php endif; ?>
        <?php if ($teamError): ?><p style="color:red;"><?= $teamError ?></p><?php endif; ?>

        <table border="1" cellpadding="5" cellspacing="0">
            <tr>
                <th>ID</th>
                <th>Naam</th>
                <th>Wachtwoord</th>
            </tr>
            <?php foreach ($teams as $data => $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['Id']) ?></td>
                    <td><?= htmlspecialchars($row['Naam']) ?></td>
                    <td><?= htmlspecialchars($row['Wachtwoord']) ?></td>
                    <td>
                    <form method="post" style="display:inline;" onsubmit="return confirm('Weet je zeker dat je dit team wilt verwijderen?');">
                        <input type="hidden" name="delete_team_id" value="<?= $row['Id'] ?>">
                        <button type="submit">Verwijder</button>
                    </form>
                </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <h3>Nieuw team toevoegen</h3>
        <form method="post">
            <input type="hidden" name="create_team" value="1">
            <label>Teamnaam:</label><br>
            <input type="text" name="team_naam"><br><br>

            <label>Wachtwoord:</label><br>
            <input type="text" name="team_wachtwoord"><br><br>

            <button type="submit">Team toevoegen</button>
        </form>

        <h2>Vragen</h2>
        <?php if ($vraagSuccess): ?><p style="color:green;"><?= $vraagSuccess ?></p><?php endif; ?>
        <?php if ($vraagError): ?><p style="color:red;"><?= $vraagError ?></p><?php endif; ?>

        <table border="1" cellpadding="5" cellspacing="0">
            <tr>
                <th>ID</th>
                <th>Vraagtekst</th>
                <th>Antwoorden</th>
                <th>Correct</th>
                <th>Punten</th>
                <th>Code</th>
                <th>Actie</th>
            </tr>
            <?php foreach ($vragen as $data => $row): ?>
                <?php if ($editVraagId == $row['Id']): ?>
                    <form method="post">
                        <tr>
                            <td><?= $row['Id'] ?></td>
                            <td><textarea name="vraagtekst"><?= htmlspecialchars($row['Vraagtekst']) ?></textarea></td>
                            <td>
                                1: <input type="text" name="antwoord1" value="<?= htmlspecialchars($row['Antwoord1']) ?>"><br>
                                2: <input type="text" name="antwoord2" value="<?= htmlspecialchars($row['Antwoord2']) ?>"><br>
                                3: <input type="text" name="antwoord3" value="<?= htmlspecialchars($row['Antwoord3']) ?>"><br>
                                4: <input type="text" name="antwoord4" value="<?= htmlspecialchars($row['Antwoord4']) ?>">
                            </td>
                            <td><input type="text" name="correct_antwoord" value="<?= htmlspecialchars($row['GoedeAntwoord']) ?>" min="1" max="4"></td>
                            <td><input type="number" name="punten" value="<?= $row['AantalPunten'] ?>"></td>
                            <td><input type="text" name="vraagcode" value="<?= htmlspecialchars($row['Code']) ?>"></td>
                            <td>
                                <input type="hidden" name="vraag_id" value="<?= $row['Id'] ?>">
                                <input type="hidden" name="update_question" value="1">
                                <button type="submit">Opslaan</button>
                            </form>
                            <form method="post" style="display:inline;">
                                <button type="submit">Annuleren</button>
                            </form>
                            </td>
                        </tr>
                <?php else: ?>
                    <tr>
                        <td><?= $row['Id'] ?></td>
                        <td><?= htmlspecialchars($row['Vraagtekst']) ?></td>
                        <td>
                            1. <?= htmlspecialchars($row['Antwoord1']) ?><br>
                            2. <?= htmlspecialchars($row['Antwoord2']) ?><br>
                            3. <?= htmlspecialchars($row['Antwoord3']) ?><br>
                            4. <?= htmlspecialchars($row['Antwoord4']) ?>
                        </td>
                        <td><?= htmlspecialchars($row['GoedeAntwoord']) ?></td>
                        <td><?= $row['AantalPunten'] ?></td>
                        <td><?= htmlspecialchars($row['Code']) ?></td>
                        <td>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="edit_question_id" value="<?= $row['Id'] ?>">
                                <button type="submit">Bewerk</button>
                            </form>
                            <form method="post" onsubmit="return confirm('Weet je zeker dat je deze vraag wilt verwijderen?');">
                                <input type="hidden" name="delete_question_id" value="<?= $row['Id'] ?>">
                                <button type="submit">Verwijder</button>
                            </form>
                        </td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>
        </table>

        <h3>Nieuwe vraag toevoegen</h3>
        <form method="post">
            <input type="hidden" name="create_question" value="1">
            
            <label>Vraagtekst:</label><br>
            <textarea name="vraagtekst" rows="3" cols="50"></textarea><br><br>

            <label>Antwoord 1:</label><br>
            <input type="text" name="antwoord1"><br>
            <label>Antwoord 2:</label><br>
            <input type="text" name="antwoord2"><br>
            <label>Antwoord 3:</label><br>
            <input type="text" name="antwoord3"><br>
            <label>Antwoord 4:</label><br>
            <input type="text" name="antwoord4"><br><br>

            <label>Correct antwoord:</label><br>
            <input type="text" name="correct_antwoord" min="1" max="4"><br><br>

            <label>Aantal punten:</label><br>
            <input type="number" name="punten" min="0"><br><br>

            <label>Vraagcode:</label><br>
            <input type="text" name="vraagcode"><br><br>

            <button type="submit">Vraag toevoegen</button>
        </form>

        <h2>Casussen</h2>
            <?php if ($casusSuccess): ?><p style="color:green;"><?= htmlspecialchars($casusSuccess) ?></p><?php endif; ?>
            <?php if ($casusError): ?><p style="color:red;"><?= htmlspecialchars($casusError) ?></p><?php endif; ?>

            <table border="1" cellpadding="5" cellspacing="0" style="overflow-x:auto; display:block; max-width:100%;">
                <tr>
                    <th>ID</th>
                    <?php foreach ($columns as $col): ?>
                        <th><?= htmlspecialchars($col) ?></th>
                    <?php endforeach; ?>
                    <th>Code</th>
                    <th>Actie</th>
                </tr>

                <?php foreach ($casussen as $row): ?>
                    <?php if ($editCasusId == $row['Id']): ?>
                        <form method="post">
                        <tr>
                            <td><?= $row['Id'] ?></td>
                            <?php foreach ($columns as $col): ?>
                                <td><input type="text" name="<?= $col ?>" value="<?= htmlspecialchars($row[$col]) ?>"></td>
                            <?php endforeach; ?>
                            <td><input type="text" name="Code" value="<?= htmlspecialchars($row['Code']) ?>"></td>
                            <td>
                                <input type="hidden" name="casus_id" value="<?= $row['Id'] ?>">
                                <input type="hidden" name="update_casus" value="1">
                                <button type="submit">Opslaan</button>
                            </td>
                        </tr>
                        </form>
                        <form method="post" style="display:inline;">
                            <button type="submit">Annuleren</button>
                        </form>
                    <?php else: ?>
                        <tr>
                            <td><?= $row['Id'] ?></td>
                            <?php foreach ($columns as $col): ?>
                                <td><?= htmlspecialchars($row[$col]) ?></td>
                            <?php endforeach; ?>
                            <td><?= htmlspecialchars($row['Code']) ?></td>
                            <td>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="edit_casus_id" value="<?= $row['Id'] ?>">
                                    <button type="submit">Bewerk</button>
                                </form>
                                <form method="post" style="display:inline;" onsubmit="return confirm('Casus verwijderen?');">
                                    <input type="hidden" name="delete_casus_id" value="<?= $row['Id'] ?>">
                                    <button type="submit">Verwijder</button>
                                </form>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </table>

            <h3>Nieuwe casus toevoegen</h3>
            <form method="post">
                <input type="hidden" name="create_casus" value="1">
                <?php foreach ($columns as $col): ?>
                    <label><?= htmlspecialchars($col) ?>:</label><br>
                    <input type="text" name="<?= $col ?>"><br><br>
                <?php endforeach; ?>
                <label>Code:</label><br>
                <input type="text" name="Code"><br><br>
                <button type="submit">Casus toevoegen</button>
            </form>
        </div>
    </body>
</html>