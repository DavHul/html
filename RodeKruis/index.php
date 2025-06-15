<?php
session_start();
require_once '../RestApi/config.php';
require_once '../RestApi/Services/AdminUserService.php';
require_once '../RestApi/Services/RodeKruisSpel/TeamService.php'; // Voeg toe als je teams wilt controleren

$adminUserObj = new AdminUsers($conn);
$teamService = new RK_team($conn); // Zorg dat deze klasse bestaat

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loginType = $_POST['login_type'];

    if ($loginType === 'admin') {
        $username = $_POST['username'];
        $password = $_POST['password'];

        $adminUser = $adminUserObj->CheckCredentials($username, $password);
        if ($adminUser != null) {
            $_SESSION['Username'] = $username;
            $_SESSION['Guid'] = $adminUser['Guid'];
            session_set_cookie_params(3600);
            ini_set('session.gc_maxlifetime', 3600);
            header('Location: dashboard.php');
            exit();
        } else {
            $error = "Ongeldige gebruikersnaam of wachtwoord.";
        }
    } elseif ($loginType === 'team') {
        $gameCode = $_POST['gamecode'];
        $teamPassword = $_POST['team_password'];

        $team = $teamService->GetTeamByCredentials($gameCode, $teamPassword); // Jij moet deze functie maken
        if ($team != null) {
            $_SESSION['TeamId'] = $team['Id'];
            $_SESSION['GameId'] = $team['SpelId'];
            $_SESSION['TeamName'] = $team['Naam'];
            header('Location: team-dashboard.php');
            exit();
        } else {
            $error = "Ongeldige spelcode of teamwachtwoord.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="style.css">
    <script>
        function toggleLogin(type) {
            document.getElementById('admin-fields').style.display = (type === 'admin') ? 'block' : 'none';
            document.getElementById('team-fields').style.display = (type === 'team') ? 'block' : 'none';
        }

        // Herstel juiste weergave bij paginalaad
        window.addEventListener('DOMContentLoaded', () => {
            const selected = document.querySelector('input[name="login_type"]:checked').value;
            toggleLogin(selected);
        });
    </script>
</head>
<body>
    <div class="container">
        <h2>Inloggen</h2>

        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="post">
            <div class="toggle">
                <label><input type="radio" name="login_type" value="admin" checked onchange="toggleLogin('admin')"> Admin</label>
                <label><input type="radio" name="login_type" value="team" onchange="toggleLogin('team')"> Team</label>
            </div>

            <div id="admin-fields">
                <label>Gebruikersnaam:</label>
                <input type="text" name="username">

                <label>Wachtwoord:</label>
                <input type="password" name="password">
            </div>

            <div id="team-fields" class="hidden">
                <label>Spelcode:</label>
                <input type="text" name="gamecode">

                <label>Team wachtwoord:</label>
                <input type="password" name="team_password">
            </div>

            <button type="submit">Inloggen</button>
        </form>
    </div>
</body>
</html>