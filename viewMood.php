<!-- Charts display page for users to track their daily mood and life habits. 
If user is not logged in, he/she is redirected back to index.php. If the user 
is logged in but has not yet filled the questionnaire for today, he/she is 
redirected to todayForm.php. -->

<?php
    session_start();

    $weeklyWellbeing = "";
    $weeklySleep = "";
    $weeklyExercise = "";

    // Set session if cookie is set.
    if (!empty($_COOKIE["id"])) {
        $_SESSION["id"] = $_COOKIE["id"];
    }

    /*************************************************************************/

    // Check if user is logged in.
    if (empty($_SESSION["id"])) {
        header("Location: index.php");
    }
    
    // If logged in...
    else {
        // Connect to database.
        include("include/connectDatabase.php");
        
        // Check if user has submitted the questionnaire for the day.
        $today = date("Y-m-d"); // Today's date in MySQL DATE format (YYYY-MM-DD).
        $query = "SELECT `username` FROM `users` WHERE `id` = ".$_SESSION["id"]." LIMIT 1";
        $row = mysqli_fetch_array(mysqli_query($link, $query));
        $username = mysqli_real_escape_string($link, $row["username"]);
        $query = "SELECT `id` FROM `entries` WHERE `username` = '".$username."' AND `date` = '".$today."' LIMIT 1";
        // If the questionnaire has not yet been filled for today...
        if (mysqli_num_rows(mysqli_query($link, $query)) == 0) {
            header("Location: todayForm.php");
        }
        
        /*********************************************************************/
        
        // If the questionnaire has been filled...
        else {
            // Get user's data for the past 7 days (including today).
            $weeklyWellbeing = array();
            $weeklySleep = array();
            $weeklyExercise = array();
            $numDays = 7; // Number of days of data we're displaying.
            $j = 0;
            for ($i = 0; $i < $numDays; $i++) {
                //$query = "SELECT `wellbeing`, `sleep`, `exercise` FROM `entries` WHERE `username` = '".$username."' AND `date` = DATE(DATE_SUB(NOW(), INTERVAL ".$i." DAY)) LIMIT 1";
                $query = "SELECT `wellbeing`, `sleep`, `exercise` FROM `entries` WHERE `username` = '".$username."' AND `date` = DATE(DATE_SUB('".$today."', INTERVAL ".$i." DAY)) LIMIT 1";
                $result = mysqli_query($link, $query);
                // If no data for this day, fill with the null character so that the chart skips this day.
                if (mysqli_num_rows($result) == 0) {
                    $weeklyWellbeing[$j] = "\0";
                    $weeklySleep[$j] = "\0";
                    $weeklyExercise[$j] = "\0";
                    $j++;
                }
                // If there is data for this day...
                else {
                    $day = mysqli_fetch_array($result);
                    $weeklyWellbeing[$j] = $day["wellbeing"];
                    $weeklySleep[$j] = $day["sleep"];
                    $weeklyExercise[$j] = $day["exercise"];
                    $j++;
                }
            }
        }
    }
?>

<!-- HTML -------------------------------------------------------------------->

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Mood Keeper - Your Mood Charts</title>
    
    <?php include("include/headInclude.php"); ?>
    
    <link href="https://fonts.googleapis.com/css?family=Tangerine|Lora" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/chartist.js/latest/chartist.min.css">
    <script src="https://cdn.jsdelivr.net/chartist.js/latest/chartist.min.js"></script>
    
<!-- CSS --------------------------------------------------------------------->
    <style type="text/css">
        html { 
            background: url(img/chartpage.jpg) no-repeat center center fixed;
            -webkit-background-size: cover;
            -moz-background-size: cover;
            -o-background-size: cover;
            background-size: cover;
        }
        body {
            padding:0;
            margin:0;
            text-align: center;
            background:none;
            color:black;
            font-family:Lora;
        }
        #container {
            min-width:600px;
            max-width:1200px;
            margin:0 auto;
            text-align:center;
            position:relative;
            top:45px;
            border-radius:40px;
            background: rgba(255, 255, 255, 0.8);
            padding:50px;
            margin-bottom:45px;
            font-weight:bold;
            font-size:18px;
        }
        .logo {
            font-family:Tangerine !important;
            font-size:50px;
        }
        .chart {
            min-width:500px;
            max-width:1000px;
            margin:0 auto;
        }
        .ct-label {
            color:black !important;
        }
    </style>
</head>

<!-- HTML body --------------------------------------------------------------->
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-light bg-faded" id="nav">
        <h2 class="navbar-brand">Mood Keeper</h2>
        <div class="form-inline float-xs-right">
            <a href='index.php?logout=1'><button class="btn btn-outline-success">Logout</button></a>
        </div>
    </nav>
    
    <div id="container">
    
    <!-- Charts -->
    <p class="logo">Your Charts</p>
    <div class="chart" id="wellbeing-chart">How you've been feeling over the past week</div><hr>
    <div class="chart" id="sleeping-chart">How you've been sleeping over the past week</div><hr>
    <div class="chart" id="exercising-chart">How you've been exercising over the past week</div><br>
    <p class="logo">Have a great rest of the day, and we hope to see you tomorrow!</p>
    </div>

<!-- JavaScript -------------------------------------------------------------->
    <?php include("include/jsInclude.php"); ?>
    
    <script type="text/javascript">
        // Default values of data arrays just in case (empty strings, so that charts won't show anything).
        var wellbeingData = "";
        var sleepData = "";
        var exerciseData = "";
        
        // Discrete x-axis values for all three charts.
        var xAxis = ['6 days ago', '5 days ago', '4 days ago', '3 days ago', '2 days ago', 'Yesterday', 'Today'];
        
        // Turn PHP arrays for wellbeing, sleep and exercise into JS arrays.
        <?php
            if ($weeklyWellbeing != "") {
                $wellbeingJS = json_encode($weeklyWellbeing);
                echo "var wellbeingData = ".$wellbeingJS.";\n";
            }
            if ($weeklySleep != "") {
                $sleepJS = json_encode($weeklySleep);
                echo "var sleepData = ".$sleepJS.";\n";
            }
            if ($weeklyExercise != "") {
                $exerciseJS = json_encode($weeklyExercise);
                echo "var exerciseData = ".$exerciseJS.";\n";
            }
        ?>
        
        // Reverse arrays for correct display on charts.
        if (wellbeingData != "" && sleepData != "" && exerciseData != "") {
            wellbeingData.reverse();
            sleepData.reverse();
            exerciseData.reverse();
        }
        
        // Wellbeing chart.
        new Chartist.Line('#wellbeing-chart', {
            labels: xAxis,
            series: [wellbeingData]
        }, {
            high:10,
            low:1,
            showLine:true,
            fullWidth: true,
            height: '250px',
            chartPadding: {
                right: 40
            },
            axisY: {
                onlyInteger: true
            },
            lineSmooth: Chartist.Interpolation.cardinal({
                fillHoles: true,
            })
        });
        
        // Sleep chart.
        new Chartist.Line('#sleeping-chart', {
            labels: xAxis,
            series: [sleepData]
        }, {
            high:10,
            low:1,
            showLine:true,
            fullWidth: true,
            height: '250px',
            chartPadding: {
                right: 40
            },
            axisY: {
                onlyInteger: true
            },
            lineSmooth: Chartist.Interpolation.cardinal({
                fillHoles: true,
            })
        });
        
        // Exercise chart.
        new Chartist.Line('#exercising-chart', {
            labels: xAxis,
            series: [exerciseData]
        }, {
            high:60,
            low:0,
            showLine:true,
            fullWidth: true,
            height: '250px',
            chartPadding: {
                right: 40
            },
            axisY: {
                onlyInteger: true
            },
            lineSmooth: Chartist.Interpolation.cardinal({
                fillHoles: true,
            })
        });
    </script>
</body>
</html>