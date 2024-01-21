<html>
    <head>
        <title>Boekhouding scouting</title>
        <link rel="stylesheet" href="src/styles.css">
        <?php
            include_once './src/login.php';
            $login_class = new login();
            $login_class->get_db_credentials();

            include_once './src/expenses_class.php';
            $expense_class = new expenses_class();
            $expense_class->get_db_credentials();
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
      <h1 style="font-size:26px;">Totaal uit</h1>
      <p style="font-size:40px;">&euro; <?php $expense_class->total_out();?></p>
    </div>
    <div class="content_upper">
      <h1>Toevoegen/aanpassen</h1>
      <?php
      if ($result == "0" OR $result == "1"){
        if (isset($_POST["change_expense"])){
          $id_number = htmlspecialchars($_POST["id_number"]);
          //echo $id_number;
          $data = $expense_class->get_data($id_number);
          $directory = getcwd()."/uploads/".$id_number."/";
          $files = scandir($directory);
          //print_r($files);
          echo '
          <form name="add_expense" method="post" action="#" enctype="multipart/form-data">
          ID: <input type="text" id="id_number" name="id_number" readonly value='.$id_number.'> <br>
          Datum: <input type="text" id="date" name="date" required value='.$data[1].'> <br>
          Titel: <input type="text" id="title" name="title" required value='.$data[2].'> <br>
          Description: <input type="text" id="description" name="description" required value='.$data[3].'> <br>
          Bedrag: <input type="number" id="amount" name="amount" required value='.$data[4].'> <br>
          Doelrekening: <input type="text" id="target" name="target" required value='.$data[5].'> <br>
          Category: <input type="text" id="category" name="category" list="categories" required value='.$data[6].'> <br>
          <datalist id="categories">
            <option value="contant">
            <option value="rekening">
          </datalist><br>
          Upload bonnetjes: <input type="file" name="the_file[]" multiple="multiple"><br>
          ';
          if ($data[7] == "incomplete"){
            echo 'Afgerond: <input type="radio" id="complete" name="complete" value="complete">Afgerond<input type="radio" id="complete" name="complete" value="incomplete" checked>Nog afronden<br>
                <input type="submit" value="Toevoegen" name="change_expense2">
              </form>';
          }else{
            echo 'Afgerond: <input type="radio" id="complete" name="complete" value="complete" checked>Afgerond<input type="radio" id="complete" name="complete" value="incomplete">Nog afronden<br>
              <input type="submit" value="Toevoegen" name="change_expense2">
            </form>';
          }
          for ($picture_nr = 2; $picture_nr < count($files); $picture_nr++){
            $picture_dir = $files[$picture_nr];
            echo "<img src='uploads/".$id_number."/".$picture_dir."' alt='Plaatje van een bonnetje' width='200' height='350'>";
          }
        }else{
          echo '
          <form name="add_expense" method="post" action="#" enctype="multipart/form-data">
          Datum: <input type="text" id="date" name="date" required> <br>
          Titel: <input type="text" id="title" name="title" required> <br>
          Description: <input type="text" id="description" name="description" required> <br>
          Bedrag: <input type="number" id="amount" name="amount" required> <br>
          Doelrekening: <input type="text" id="target" name="target" required> <br>
          Category: <input type="text" id="category" name="category" list="categories" required> <br>
          <datalist id="categories">
            <option value="contant">
            <option value="rekening">
          </datalist>
          Upload bonnetjes: <input type="file" name="the_file[]" multiple="multiple"><br>
          Afgerond: <input type="radio" id="complete" name="complete" value="complete">Afgerond<input type="radio" id="complete" name="complete" value="incomplete">Nog afronden<br>
          <input type="submit" value="Toevoegen" name="add_expense">
        </form>';
        }

        if (isset($_POST["add_expense"])){
          $date = htmlspecialchars($_POST["date"]);
          $title = htmlspecialchars($_POST["title"]);
          $description = htmlspecialchars($_POST["description"]);
          $amount = htmlspecialchars($_POST["amount"]);
          $target = htmlspecialchars($_POST["target"]);
          $category = htmlspecialchars($_POST["category"]);
          $complete = htmlspecialchars($_POST["complete"]);
          $expense_class->add_expense($date, $title, $description, $amount, $target, $category, $complete);
          
          if (isset($_FILES['the_file']['name'])){
            $currentDirectory = getcwd();
            $last_id = $expense_class->get_last_id();
            $uploadDirectory = "/uploads/".$last_id."/";
            if (! is_dir($currentDirectory .$uploadDirectory)){
              mkdir($currentDirectory .$uploadDirectory);
            }
            for ($file_nr = 0; $file_nr < count($_FILES['the_file']['name']); $file_nr++){
              $errors = []; // Store errors here

              $fileExtensionsAllowed = ['jpeg','jpg','png']; // These will be the only file extensions allowed 

              $fileName = $_FILES['the_file']['name'][$file_nr];
              $fileSize = $_FILES['the_file']['size'][$file_nr];
              $fileTmpName  = $_FILES['the_file']['tmp_name'][$file_nr];
              $fileType = $_FILES['the_file']['type'][$file_nr];
              $fileExtension = strtolower(end(explode('.', $fileName)));

              $uploadPath = $currentDirectory . $uploadDirectory . basename($fileName); 
              echo $uploadPath;

              if (! in_array($fileExtension,$fileExtensionsAllowed)) {
                $errors[] = "This file extension is not allowed. Please upload a JPEG or PNG file";
              }

              if ($fileSize > 400000000) {
                $errors[] = "File exceeds maximum size (400MB)";
              }

              if (empty($errors)) {
                $didUpload = move_uploaded_file($fileTmpName, $uploadPath);

                if ($didUpload) {
                  echo "The file " . basename($fileName) . " has been uploaded";
                } else {
                  echo "An error occurred. Please contact the administrator.";
                }
              } else {
                foreach ($errors as $error) {
                  echo $error . "These are errors" . "\n";
                }
              }

            }
          }
          header("Refresh:0; url=expense.php");
        }

        if (isset($_POST["change_expense2"])){
          $id_number = htmlspecialchars($_POST["id_number"]);
          $date = htmlspecialchars($_POST["date"]);
          $title = htmlspecialchars($_POST["title"]);
          $description = htmlspecialchars($_POST["description"]);
          $amount = htmlspecialchars($_POST["amount"]);
          $target = htmlspecialchars($_POST["target"]);
          $category = htmlspecialchars($_POST["category"]);
          $complete = htmlspecialchars($_POST["complete"]);
          $expense_class->change_expense($id_number, $date, $title, $description, $amount, $target, $category, $complete);
          
          if (isset($_FILES['the_file']['name'])){
            $currentDirectory = getcwd();
            $last_id = $expense_class->get_last_id();
            $uploadDirectory = "/uploads/".$last_id."/";
            if (! is_dir($currentDirectory .$uploadDirectory)){
              mkdir($currentDirectory .$uploadDirectory);
            }
            for ($file_nr = 0; $file_nr < count($_FILES['the_file']['name']); $file_nr++){
              $errors = []; // Store errors here

              $fileExtensionsAllowed = ['jpeg','jpg','png']; // These will be the only file extensions allowed 

              $fileName = $_FILES['the_file']['name'][$file_nr];
              $fileSize = $_FILES['the_file']['size'][$file_nr];
              $fileTmpName  = $_FILES['the_file']['tmp_name'][$file_nr];
              $fileType = $_FILES['the_file']['type'][$file_nr];
              $fileExtension = strtolower(end(explode('.', $fileName)));

              $uploadPath = $currentDirectory . $uploadDirectory . basename($fileName); 
              echo $uploadPath;

              if (! in_array($fileExtension,$fileExtensionsAllowed)) {
                $errors[] = "This file extension is not allowed. Please upload a JPEG or PNG file";
              }

              if ($fileSize > 400000000) {
                $errors[] = "File exceeds maximum size (400MB)";
              }

              if (empty($errors)) {
                $didUpload = move_uploaded_file($fileTmpName, $uploadPath);

                if ($didUpload) {
                  echo "The file " . basename($fileName) . " has been uploaded";
                } else {
                  echo "An error occurred. Please contact the administrator.";
                }
              } else {
                foreach ($errors as $error) {
                  echo $error . "These are errors" . "\n";
                }
              }

            }
          }
          header("Refresh:0; url=expense.php");
        }
      }
      ?>
    </div>
    <div class="content_lower">
      <h1>Geschiedenis</h1>
      <table style="width:100%">
        <tr><th>Id</th><th>Date</th><th>Title</th><th>Description</th><th>Amount</th><th>Target</th><th>Category</th><th>Finished</th></tr>
        <?php $expense_class->print_html_table();?>
      </table>
    </div>
    </body>
</html>
