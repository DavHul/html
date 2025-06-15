<?php
    session_start();
    require_once '../RestApi/config.php';
    require_once '../RestApi/Services/RodeKruisSpel/TeamService.php';
    require_once '../RestApi/Services/RodeKruisSpel/SpelService.php';
    require_once '../RestApi/Services/RodeKruisSpel/VraagService.php';
    require_once '../RestApi/Services/RodeKruisSpel/AntwoordService.php';
    require_once '../RestApi/Services/RodeKruisSpel/CasusService.php';
    require_once '../RestApi/Services/RodeKruisSpel/KoppelCodeService.php';
    require_once '../RestApi/Services/RodeKruisSpel/CasusBeoordelingService.php';


    $spelObj = new RK_spel($conn);
    $teamObj = new RK_team($conn);
    $questionObj = new RK_vraag($conn);
    $answerObj = new RK_antwoord($conn);
    $casusObj = new RK_casus($conn);
    $koppelObj = new RK_KoppelCode($conn);
    $beoordelingObj = new RK_CasusBeoordeling($conn);

    $TeamId = $_SESSION['TeamId'];
    $GameId = $_SESSION['GameId'];
    $TeamName = $_SESSION['TeamName'];

    $error = '';
    $vraag = null;
    $beoordeling = null;
    $casus = null;
    $previousAnswer = null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['vraagcode'])) {
        $code = trim($_POST['vraagcode']);

        // Stap 1: Zoek code in RK_KoppelCode
        $koppelData = $koppelObj->GetDataByCodeAndSpelId($code, $GameId);
        if ($koppelData != null) {
            $koppelData = $koppelData[0];
            if ($koppelData['VraagId'] != -1) {
                // Vraag ophalen
                $vraag = $questionObj->GetQuestionById($koppelData['VraagId']);
                if ($vraag) {
                    $previousAnswer = $answerObj->GetAnswerByQuestionIdAndTeamId(intval($vraag["Id"]), $TeamId);
                } else {
                    $error = "Vraag niet gevonden.";
                }
            } elseif ($koppelData['CasusId'] != -1) {
                // Casus ophalen
                $casus = $casusObj->GetCasusById($koppelData['CasusId']);
                if ($casus) {
                    $beoordeling = $beoordelingObj->GetBeoordelingByTeamAndCasusId($TeamId, $casus['Id']);
                } else {
                    $error = "Casus niet gevonden.";
                }
            } else {
                $error = "Ongeldige koppeling in database.";
            }
        } else {
            $error = "Code niet gevonden.";
        }
    }

    // Stap 2: Antwoord opsturen
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['antwoord'])) {
        $vraagId = $_POST['VraagId'];
        $gegevenAntwoord = $_POST['antwoord'];
        $answerObj->SubmitAnswer($vraagId, $TeamId, $gegevenAntwoord);
        $vraag = null;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['score_submit'])) {
        $puntenCentralist = intval($_POST['punten_centralist']);
        $puntenSlachtoffer = intval($_POST['punten_slachtoffer']);
        $casusId = intval($_POST['casus_id']);

        $bestaandeBeoordeling = $beoordelingObj->GetBeoordelingByTeamAndCasusId($TeamId, $casusId);

        if ($bestaandeBeoordeling) {
            $beoordelingObj->UpdateBeoordeling($bestaandeBeoordeling['Id'], $casusId, $TeamId, $puntenCentralist, $puntenSlachtoffer);
        } else {
            $beoordelingObj->CreateBeoordeling($casusId, $TeamId, $puntenCentralist, $puntenSlachtoffer);
        }

        // Herlaad beoordeling voor correcte weergave
        $beoordeling = $beoordelingObj->GetBeoordelingByTeamAndCasusId($TeamId, $casusId);
    }
?>
<!DOCTYPE html>
<html lang="nl">
    <head>
        <meta charset="UTF-8">
        <title>Team <?= htmlspecialchars($TeamName) ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <div class="container">
            <h1>Welkom, team <?= htmlspecialchars($TeamName) ?></h1>

            <!-- Code invoeren -->
            <form method="post">
                <label>Voer code in:</label><br>
                <input type="text" name="vraagcode" required><br><br>
                <button type="submit">Zoek</button>
            </form>

            <?php if ($error): ?>
                <p style="color:red;"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <?php if ($vraag): ?>
                <hr>
                <form method="post">
                    <p><strong><?= htmlspecialchars($vraag['Vraagtekst']) ?></strong></p>
                    <input type="hidden" name="VraagId" value="<?= $vraag['Id'] ?>">

                    <?php for ($i = 1; $i <= 4; $i++): ?>
                        <?php $antwoord = $vraag["Antwoord$i"]; ?>
                        <?php if (!empty($antwoord)): ?>
                            <label>
                                <input type="radio" name="antwoord" value="<?= htmlspecialchars($antwoord) ?>"
                                    <?= ($previousAnswer && $previousAnswer['GegevenAntwoord'] === $antwoord) ? 'checked' : '' ?>>
                                <?= htmlspecialchars($antwoord) ?>
                            </label><br>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <br>
                    <button type="submit">Verzend antwoord</button>
                </form>

            <?php elseif ($casus): ?>
                <hr>
                <h2>Casusinformatie</h2>
                <table>
                    <?php
                    $velden = [
                        'Naam', 'MaximaalPunten', 'InformatieAnder', 'InformatieZelf', 'NaamSlachtoffer', 'GeboorteDatum',
                        'Geslacht', 'Woonplaats', 'Toedracht', 'LuchtwegStatus', 'AdemhalingFrequentie', 'AdemhalingSymetrie',
                        'HartslagFrequentie', 'HartslagRitme', 'HartslagKracht', 'BewustzijnScore', 'Temperatuur',
                        'Opmerkingen', 'Allergie', 'Medicatie', 'ZiekteGeschiedenis', 'Letsel', 'Omstandigheden'
                    ];
                    foreach ($velden as $veld):
                        if (isset($casus[$veld])):
                    ?>
                        <tr>
                            <th><?= htmlspecialchars($veld) ?></th>
                            <td><?= nl2br(htmlspecialchars($casus[$veld])) ?></td>
                        </tr>
                    <?php endif; endforeach; ?>
                </table>
                <?php if ($casus['MaximaalPunten'] == 20): ?>
                <h2>Beoordeel deze casus</h2>
                    <form method="post">
                        <input type="hidden" name="casus_id" value="<?= htmlspecialchars($casus['Id']) ?>">

                        <!-- Verborgen input voor punten van de centralist -->
                        <input type="hidden" name="punten_centralist" value="<?= $beoordeling ? htmlspecialchars($beoordeling['PuntenCentralist']) : 0 ?>">

                        <!-- Invoerveld voor punten slachtoffer -->
                        <label>Punten slachtoffer (0-10):</label>
                        <input type="number" name="punten_slachtoffer" min="0" max="10"
                            value="<?= $beoordeling ? htmlspecialchars($beoordeling['PuntenSlachtoffer']) : '' ?>" required>

                        <button type="submit" name="score_submit">Verzend score</button>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </body>
</html>