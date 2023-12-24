<html>
    <head>
        <title>Boekhouding scouting</title>
        <link rel="stylesheet" href="src/styles.css">
        <?php
            include_once './src/login.php';
            $login_class = new login();

            include_once './src/income_class.php';
            $income_class = new income_class();
        ?>
    </head>
    <body>
    <?php
        $result = $login_class->check_login();
        if ($result == "false"){
            header("Refresh:1; url=index.php");
        }
    ?>
    <div class="topnav">
        <a href="./total.php">Home</a>
        <a href="./income.php">Inkomsten</a>
        <a href="./expense.php">Uitgaven</a>
        <a href="./index.php">Uitloggen</a>
    </div>
    <div class="stats">
    <h1 style="font-size:26px;">Totaal in</h1>
      <p style="font-size:40px;">&euro; <?php $income_class->total_in();?></p>
    </div>
    <div class="content_upper">
      <h1>Toevoegen/aanpassen</h1>
      <form name="add_expense" method="post" action="#">
        Datum: <input type="text" id="date" name="date" required> <br>
        Titel: <input type="text" id="title" name="title" required> <br>
        Description: <input type="text" id="description" name="description" required> <br>
        Bedrag: <input type="number" id="amount" name="amount" required> <br>
        Oorsprong: <input type="text" id="origin" name="origin" required> <br>
        Category: <input type="text" id="category" name="category" list="categories" required> <br>
        <datalist id="categories">
          <option value="contant">
          <option value="rekening">
        </datalist>
        Afgerond: <input type="radio" id="complete" name="complete" value="complete">Afgerond<input type="radio" id="complete" name="complete" value="incomplete">Nog afronden<br>
        <input type="submit" value="Toevoegen" name="add_expense">
      </form>
      <?php
      if (isset($_POST["add_expense"])){
        $date = htmlspecialchars($_POST["date"]);
        $title = htmlspecialchars($_POST["title"]);
        $description = htmlspecialchars($_POST["description"]);
        $amount = htmlspecialchars($_POST["amount"]);
        $origin = htmlspecialchars($_POST["origin"]);
        $complete = htmlspecialchars($_POST["complete"]);
        $category = htmlspecialchars($_POST["category"]);
        $income_class->add_income($date, $title, $description, $amount, $origin, $category, $complete);
        header("Refresh:1; url=income.php");
      }?>
    </div>
    <div class="content_lower">
      <h1>Geschiedenis</h1>
      <table style="width:100%">
        <tr><th>Id</th><th>Date</th><th>Title</th><th>Description</th><th>Amount</th><th>Category</th><th>Origin</th><th>Finished</th></tr>
        <?php $income_class->print_html_table();?>
      </table>
    </div>
    </body>
</html>