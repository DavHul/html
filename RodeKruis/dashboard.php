<?php
session_start();
require_once '../RestApi/config.php';
require_once '../RestApi/Services/AdminUserService.php';
require_once '../RestApi/Services/RodeKruisSpel/SpelService.php';
$spelObj = new RK_spel($conn);
$adminUserObj = new AdminUsers($conn);

$success = '';
$error = '';

// Nieuw spel toevoegen via formulier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_game'])) {
    $naam = $_POST['naam'];
    $admin_id = $_POST['admin_id'];
    $code = $_POST['code'];

    if ($naam && $admin_id && $code) {
        $success = $spelObj->CreateGame($naam, $admin_id, $code);
    } else {
        $error = "Vul alle velden in.";
    }
}

// Spel selecteren via link (en opslaan in sessie)
if (isset($_GET['select_id'])) {
    $_SESSION['selected_game_id'] = $_GET['select_id'];
    header("Location: gameSettings.php");
    exit();
}

// Haal spellen op
$user = $adminUserObj->CheckGuid($_SESSION['Guid']);
$result = $spelObj->GetAllGamesByAdminId($user['Id']);
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Spellenoverzicht</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Spellenoverzicht</h1>

        <?php if ($success): ?>
            <p class="message success"><?= htmlspecialchars($success) ?></p>
        <?php endif; ?>
        <?php if ($error): ?>
            <p class="message error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Naam</th>
                    <th>Admin ID</th>
                    <th>Code</th>
                    <th>Instellen</th>
                    <th>Monitor</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['Id']) ?></td>
                        <td><?= htmlspecialchars($row['Naam']) ?></td>
                        <td><?= htmlspecialchars($row['AdminId']) ?></td>
                        <td><?= htmlspecialchars($row['Code']) ?></td>
                        <td>
                            <a class="button" href="?select_id=<?= $row['Id'] ?>">Instellen</a>
                        </td>
                        <td>
                            <a class="button" href="monitorGame.php?game_id=<?= $row['Id'] ?>">Monitor</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h2>Nieuw Spel Toevoegen</h2>
        <form method="post">
            <input type="hidden" name="create_game" value="1">

            <label for="naam">Naam:</label>
            <input type="text" name="naam" id="naam" required>

            <input type="hidden" name="admin_id" value="<?= htmlspecialchars($user['Id']) ?>">

            <label for="code">Code:</label>
            <input type="text" name="code" id="code" required><br><br>

            <button type="submit">Spel aanmaken</button>
        </form>
    </div>
</body>
</html>
