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
<html>
<head>
    <title>Spellenoverzicht</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { padding: 8px 12px; border: 1px solid #ccc; }
        a.button, button {
            display: inline-block;
            padding: 8px 12px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        a.button:hover, button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <h1>Spellenoverzicht</h1>

    <?php if ($success): ?><p style="color:green;"><?= $success ?></p><?php endif; ?>
    <?php if ($error): ?><p style="color:red;"><?= $error ?></p><?php endif; ?>

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
            <?php foreach ($result as $data => $row):?>
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
        <label>Naam:</label><br>
        <input type="text" name="naam"><br><br>

        <input type="hidden" name="admin_id" value=<?php $user['Id']?>>

        <label>Code:</label><br>
        <input type="text" name="code"><br><br>

        <button type="submit">Spel aanmaken</button>
    </form>
</body>
</html>