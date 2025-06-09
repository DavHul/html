<?php
session_start();
require_once '../RestApi/config.php';
require_once '../RestApi/Services/RodeKruisSpel/TeamService.php';
require_once '../RestApi/Services/RodeKruisSpel/CasusBeoordelingService.php';
require_once '../RestApi/Services/RodeKruisSpel/AntwoordService.php';
require_once '../RestApi/Services/RodeKruisSpel/CasusService.php';

$teamObj = new RK_team($conn);
$beoordelingObj = new RK_CasusBeoordeling($conn);
$antwoordObj = new RK_antwoord($conn);
$casusObj = new RK_casus($conn);

if (!isset($_GET['game_id'])) {
    echo "Geen spel geselecteerd.";
    exit;
}

$gameId = intval($_GET['game_id']);
$teams = $teamObj->GetAllTeamsByGameId($gameId);
$casussen = $casusObj->GetAllCasussenByGameId($gameId);

$beoordeling = null;

// Beoordeling ophalen als beide zijn gekozen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['team_id'], $_POST['casus_id'])) {
    $teamId = intval($_POST['team_id']);
    $casusId = intval($_POST['casus_id']);
    $beoordeling = $beoordelingObj->GetBeoordelingByTeamAndCasusId($teamId, $casusId);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['score_submit'])) {
    $teamId = intval($_POST['team_id']);
    $casusId = intval($_POST['casus_id']);
    $puntenCentralist = intval($_POST['punten_centralist']);

    $bestaande = $beoordelingObj->GetBeoordelingByTeamAndCasusId($teamId, $casusId);
    if ($bestaande) {
        $beoordelingObj->UpdateBeoordeling($bestaande['Id'], $casusId, $teamId, $puntenCentralist, $bestaande['PuntenSlachtoffer']);
        echo "<p style='color:green;'>Beoordeling bijgewerkt.</p>";
    } else {
        $beoordelingObj->CreateBeoordeling($casusId, $teamId, $puntenCentralist, 0);
        echo "<p style='color:green;'>Beoordeling aangemaakt.</p>";
    }

    header("Location: " . $_SERVER['PHP_SELF']. "?game_id=".$gameId);
    exit();
}

$selectedTeamId = $_POST['team_id'] ?? null;
$selectedCasusId = $_POST['casus_id'] ?? null;
$beoordeling = null;

if ($selectedTeamId && $selectedCasusId) {
    $beoordeling = $beoordelingObj->GetBeoordelingByTeamAndCasusId($selectedTeamId, $selectedCasusId);
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Monitoring Spel <?= htmlspecialchars($gameId) ?></title>
    <style>
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f5f5f5; }
    </style>
</head>
<body>
    <h1>Monitoring voor spel ID <?= htmlspecialchars($gameId) ?></h1>
    <h3>Selecteer team en casus</h3>
        <form id="selectieFormulier" method="post">
            <label>Team:</label><br>
            <select name="team_id" id="teamSelect" required>
                <option value="">-- Kies een team --</option>
                <?php foreach ($teams as $team): ?>
                    <option value="<?= $team['Id'] ?>" <?= $selectedTeamId == $team['Id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($team['Naam']) ?>
                    </option>
                <?php endforeach; ?>
            </select><br><br>

            <label>Casus:</label><br>
            <select name="casus_id" id="casusSelect" required>
                <option value="">-- Kies een casus --</option>
                <?php foreach ($casussen as $casus): ?>
                    <option value="<?= $casus['Id'] ?>" <?= $selectedCasusId == $casus['Id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($casus['Naam']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
        <?php if ($selectedTeamId && $selectedCasusId): ?>
            <hr>
            <h3>Beoordeel casus voor geselecteerd team</h3>
            <form method="post">
                <input type="hidden" name="team_id" value="<?= $selectedTeamId ?>">
                <input type="hidden" name="casus_id" value="<?= $selectedCasusId ?>">

                <label>Punten centralist (0-10):</label>
                <input type="number" name="punten_centralist" min="0" max="10"
                    value="<?= $beoordeling ? htmlspecialchars($beoordeling['PuntenCentralist']) : '' ?>" required><br><br>

                <button type="submit" name="score_submit">Verzend score</button>
            </form>
        <?php endif; ?>
        <script>
        document.addEventListener('DOMContentLoaded', () => {
            const teamSelect = document.getElementById('teamSelect');
            const casusSelect = document.getElementById('casusSelect');
            const form = document.getElementById('selectieFormulier');

            function checkSubmit() {
                if (teamSelect.value && casusSelect.value) {
                    form.submit();
                }
            }

            teamSelect.addEventListener('change', checkSubmit);
            casusSelect.addEventListener('change', checkSubmit);
        });
        </script>
    <table>
        <thead>
            <tr>
                <th>Teamnaam</th>
                <th>Punten Casussen</th>
                <th>Punten Vragen</th>
                <th>Totaal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($teams as $team): ?>
                <?php
                    $puntenCasussen = 0;
                    $puntenVragen = 0;

                    // Casuspunten optellen
                    $beoordelingen = $beoordelingObj->GetAllBeoordelingenByTeamId($team['Id']);
                    foreach ($beoordelingen as $b) {
                        $puntenCasussen += intval($b['PuntenCentralist']) + intval($b['PuntenSlachtoffer']);
                    }

                    // Vraagpunten optellen
                    $antwoorden = $antwoordObj->GetAllAnswersByTeamId($team['Id']);
                    foreach ($antwoorden as $a) {
                        $puntenVragen += intval($a['PuntenVerdiend']);
                    }

                    $totaal = $puntenCasussen + $puntenVragen;
                ?>
                <tr>
                    <td><?= htmlspecialchars($team['Naam']) ?></td>
                    <td><?= $puntenCasussen ?></td>
                    <td><?= $puntenVragen ?></td>
                    <td><?= $totaal ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
