<!-- Questionnaire page for users to answer daily behavioral questions. This is
where the user gets redirected upon login. If user is not logged in, he/she is
redirected back to index.php. If the user is logged in but has already filled
the questionnaire for today, he/she is redirected to viewMood.php. Upon 
submission of the questionnaire, the user gets redirected to viewMood.php. -->

<?php
    session_start();

    $username = "";
    $msg = "";

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
        
        // Check if user has already submitted the questionnaire for the day.
        $today = date("Y-m-d"); // Today's date in MySQL DATE format (YYYY-MM-DD).
        $query = "SELECT `username` FROM `users` WHERE `id` = ".$_SESSION["id"]." LIMIT 1";
        $row = mysqli_fetch_array(mysqli_query($link, $query));
        $username = mysqli_real_escape_string($link, $row["username"]);
        $query = "SELECT `id` FROM `entries` WHERE `username` = '".$username."' AND `date` = '".$today."' LIMIT 1";
        // If the questionnaire has already been filled for today...
        if (mysqli_num_rows(mysqli_query($link, $query)) > 0) {
            header("Location: viewMood.php");
        }
        
        /*********************************************************************/
        
        // If the questionnaire hasn't been filled yet...
        else {
            // Upon receiving a POST request (through form submission)...
            if ($_POST) {
                // Wellbeing can take a value from 1 to 10.
                $wellbeing = (int) $_POST["wellbeing"];
                // Sleep can take a value from 1 to 10.
                $sleep = (int) $_POST["sleep"];
                // Exercise can take a value from 0 to 60 with steps of 5.
                $exercise = (int) $_POST["exercise"];
                // Add this entry into the database.
                $query = "INSERT INTO `entries` (`username`, `date`, `wellbeing`, `sleep`, `exercise`) VALUES('".$username."', '".$today."', ".$wellbeing.", ".$sleep.", ".$exercise.")";
                if (!mysqli_query($link, $query)) {
                    $msg = "<p>Submission failed. Please try again later.</p>";
                }
                // If entry added successfully...
                else {
                    // Redirect to answers display page.
                    header("Location: viewMood.php");
                }
            }
        }
    }
?>

<!-- HTML -------------------------------------------------------------------->

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Mood Keeper - Today's Mood</title>
    
    <?php include("include/headInclude.php"); ?>
    
    <link href="https://fonts.googleapis.com/css?family=Lora" rel="stylesheet">
    
<!-- CSS --------------------------------------------------------------------->
    <style type="text/css">
        html { 
            background: url(img/formpage.jpg) no-repeat center center fixed;
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
            color:aliceblue;
            font-family:Lora;
        }
        #nav {
            margin-top:0;
        }
        #container {
            width:70%;
            max-width:800px;
            margin:0 auto;
            text-align:center;
            position:relative;
            top:45px;
            border-radius:40px;
            background: rgba(44, 44, 44, 0.4);
            padding:50px;
            margin-bottom:45px;
            font-weight:bold;
            font-size:18px;
        }
        #greeting {
            font-size:30px;
        }
        .form-control {
            max-width:200px !important;
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
    
    <!-- Error messages, if any (there shouldn't ever be any here) -->
    <div id="msg"><?php if ($msg != "") { echo "<div class='alert alert-danger' role='alert'>".$msg."</div>"; } ?></div>
        
    <!-- Daily questionnaire -->
    <form method="post">
        <p id="greeting">Hey <?php if (!empty($username)) { echo $username; } ?>!<br>We're glad to have you. Tell us about your day!</p>
        <div class="form-group" align="center">
            <label for="wellbeing">From 1 being the worst to 10 being the best, how are you feeling today?</label>
            <select class="form-control" name="wellbeing" id="wellbeing">
                <?php for ($i = 1; $i <= 10; $i++) : ?>
                <option><?php echo $i; ?></option><?php endfor; ?>
            </select>
        </div>
        <hr>
        <div class="form-group" align="center">
            <label for="sleep">How much sleep did you get last night?</label>
            <select class="form-control" name="sleep" id="sleep">
                <option>1 hour or less</option>
                <?php for ($i = 2; $i <= 9; $i++) : ?>
                <option><?php echo $i; ?></option><?php endfor; ?>
                <option>10 hours or more</option>
            </select>
        </div>
        <hr>
        <div class="form-group" align="center">
            <label for="exercise">How much physical exercise did you get today?</label>
            <select class="form-control" name="exercise" id="exercise">
                <option>0</option>
                <?php for ($i = 5; $i <= 55; $i+=5) : ?>
                <option><?php echo $i." minutes"; ?></option><?php endfor; ?>
                <option>60 minutes or more</option>
            </select>
        </div>
        <div class="form-group">
            <input type="submit" name="submit" class="btn btn-success" value="Submit">
        </div>
    </form>
        
    </div>
    
<!-- JavaScript -------------------------------------------------------------->
    <?php include("include/jsInclude.php"); ?>
</body>
</html>