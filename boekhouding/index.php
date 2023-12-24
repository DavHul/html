<html>
    <head>
        <title>Boekhouding scouting</title>
        <?php
          include_once './src/login.php';
          $login_class = new login();
        ?>
        <link rel="stylesheet" href="src/styles.css">
    </head>
    <body>
    <?php
    $result = $login_class->check_login();
    if ($result == "false"){
      echo '<div class="topnav">
          <a href="./index.php">Inloggen</a>
        </div>
        <div id="login_form" class="total_screen">
        ';
        if (($_SERVER["REQUEST_METHOD"] != "POST") or (isset($_POST["inloggen"]))){
          echo "<p style='color:white;font-size: 40px;'>Login</p>
          <div>
          <p>Voer uw gebruikersnaam en wachtwoord in om door te gaan.</p>
          <form name='inloggen' method='post' action='#' >					
            Username: <input type='textfield' name='username' ><br>
            Password: <input type='password' name='password'><br>
            <input type='submit' value='Inloggen' name='inloggen_2'><br><br>
          </form>
          </div>";
        }

        if (isset($_POST["inloggen_2"])){
          $username = htmlspecialchars($_POST["username"]);
          $password = htmlspecialchars($_POST["password"]);
          $result = $login_class->log_in($username, $password);
          echo "<h1>Controleren inloggegevens</h1>";
          if ($result == "true"){
            header("Refresh:2; url=total.php");
          }else{
            header("Refresh:2; url=index.php");
          }
        }
      
      echo "</div>";
    }else{
      $login_class->log_out();
      echo '<div class="topnav">
          <a href="./index.php">Inloggen</a>
        </div>
        <div id="login_form" class="total_screen">
          <h1>U bent uitgelogd</h1>
        </div>';
      header("Refresh:5; url=index.php");
    }
    
  ?></body>
</html>