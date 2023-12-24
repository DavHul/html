<html>
    <head>
        <title>Boekhouding scouting</title>
        <?php
            include_once './src/login.php';
            $login_class = new login();

            include_once './src/total_balance.php';
            $total_balance = new total_balance();
        ?>
        <link rel="stylesheet" href="src/styles.css">
        <script
            src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js">
        </script>
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
            <h1 style="font-size:26px;">Balans</h1>
            <p style="font-size:40px;">&euro; <?php $total_balance->print_balance();?></p>
        </div>
            <div class="content_upper">
            <h1>Statestieken</h1>
            <canvas id="linechart_balance" style="width:100%;max-width:400px;height:31%;float:left"></canvas>
            <canvas id="donutchart_balance" style="width:100%;max-width:400px;height:31%;float:right"></canvas>
            <script>
                const dates = <?php echo json_encode($total_balance->get_dates()); ?>;
                const balance = <?php echo json_encode($total_balance->get_balances()); ?>;

                new Chart("linechart_balance", {
                type: "line",
                data: {
                    labels: dates,
                    datasets: [{
                    fill: false,
                    lineTension: 0,
                    backgroundColor: "rgba(255,255,255,1.0)",
                    borderColor: "rgba(255,255,255,0.5)",
                    data: balance
                    }]
                },
                options: {
                    legend: {display: false},
                    
                }
                });

                var label = ["Rekening", "Contant"];
                var data_balans = <?php echo json_encode($total_balance->get_balance_split()); ?>;
                var barColors = [
                "#b91d47",
                "#00aba9",
                ];

                new Chart("donutchart_balance", {
                type: "doughnut",
                data: {
                    labels: label,
                    datasets: [{
                    backgroundColor: barColors,
                    data: data_balans
                    }]
                },
                options: {
                    title: {
                    display: true,
                    text: "Balans verdeling"
                    }
                }
                });
            </script>
        </div>
        <div class="content_lower">
            <h1>Geschiedenis</h1>
            <table style="width:100%">
                <tr><th>Id</th><th>Transaction id</th><th>Direction</th><th>Date</th><th>Amount</th><th>Balance before</th><th>Balance after</th></tr>
                <?php $total_balance->print_html_table();?>
            </table>
        </div>
    </body>
</html>