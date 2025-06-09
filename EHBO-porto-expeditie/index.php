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
<html>
<head>
    <title>Login</title>
    <style>
        .hidden { display: none; }
        .toggle { margin-bottom: 10px; }
    </style>
    <script>
        function toggleLogin(type) {
            document.getElementById('admin-fields').style.display = (type === 'admin') ? 'block' : 'none';
            document.getElementById('team-fields').style.display = (type === 'team') ? 'block' : 'none';
        }
    </script>
</head>
<body>
    <h2>Inloggen</h2>

    <?php if ($error): ?>
        <p style="color:red;"><?= $error ?></p>
    <?php endif; ?>

    <form method="post">
        <div class="toggle">
            <label><input type="radio" name="login_type" value="admin" checked onchange="toggleLogin('admin')"> Admin</label>
            <label><input type="radio" name="login_type" value="team" onchange="toggleLogin('team')"> Team</label>
        </div>

        <div id="admin-fields">
            <label>Gebruikersnaam:</label><br>
            <input type="text" name="username"><br>
            <label>Wachtwoord:</label><br>
            <input type="password" name="password"><br>
        </div>

        <div id="team-fields" class="hidden">
            <label>Spelcode:</label><br>
            <input type="text" name="gamecode"><br>
            <label>Team wachtwoord:</label><br>
            <input type="password" name="team_password"><br>
        </div>

        <button type="submit">Inloggen</button>
    </form>

    <script>
        // Zorgt ervoor dat juiste formulier bij paginaherlaad correct zichtbaar is
        const selected = document.querySelector('input[name="login_type"]:checked').value;
        toggleLogin(selected);
    </script>
</body>
</html>