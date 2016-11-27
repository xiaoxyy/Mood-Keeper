<!-- Homepage for signing in and logging in. This is where the user gets
redirected if he/she gets logged out. Upon successful sign up or login (or if
the user is already logged in), the user gets redirected to todayForm.php. -->

<?php
    session_start();
 
    $msg = "";

    // If logout GET request received...
    if (array_key_exists("logout", $_GET)) {
        unset($_SESSION["id"]);
        setcookie("id", "", time() - 3600);
    }
    // Otherwise, if already logged in...
    else if (!empty($_SESSION["id"]) || !empty($_COOKIE["id"])) {
        header("Location: todayForm.php");
    }

    /*************************************************************************/

    // Upon receiving a POST request (through form submission)...
    if ($_POST) {
        // Connect to database.
        include("include/connectDatabase.php");
        
        // Validate form and only proceed if form is valid.
        if ($_POST["username"] == "") {
            $msg .= "<br>The username field is required.";
        }
        if ($_POST["pw"] == "") {
            $msg .= "<br>The password field is required.";
        }
        if (array_key_exists("signup", $_POST)) {
            if ($_POST["pw-conf"] == "") {
                $msg .= "<br>The password confirmation field is required.";
            }
            if ($_POST["pw"] != $_POST["pw-conf"]) {
                $msg .= "<br>The password confirmation doesn't match.";
            }
        }
        if ($msg != "") {
            $msg = "<p>The form is invalid due to the following error(s):".$msg."</p>";
        }
        
        /*********************************************************************/
        
        // If the Sign Up button was pressed...
        else if (array_key_exists("signup", $_POST)) {
            // Check that this username isn't taken.
            $username = $_POST["username"];
            $username = mysqli_real_escape_string($link, $username);
            $query = "SELECT `id` FROM `users` WHERE `username` = '".$username."' LIMIT 1";
            if (mysqli_num_rows(mysqli_query($link, $query)) > 0) {
                $msg = "<p>This username is taken.</p>";
            }
            // If not, create a new account.
            else {
                $pw = $_POST["pw"];
                $pw = mysqli_real_escape_string($link, $pw);
                $query = "INSERT INTO `users` (`username`, `password`) VALUES('".$username."', '".$pw."')";
                if (!mysqli_query($link, $query)) {
                    $msg = "<p>Sign up failed. Please try again later.</p>";
                }
                // If creation is successful...
                else {
                    // Hash password.
                    $id = mysqli_insert_id($link);
                    $hash = md5(md5($id).$_POST["pw"]);
                    $query = "UPDATE `users` SET `password` = '".$hash."' WHERE `id` =".$id." LIMIT 1";
                    mysqli_query($link, $query);
                    
                    // Set session variable.
                    $_SESSION["id"] = $id;
                    
                    // If requested, set cookie as well.
                    if ($_POST["checkbox"] == "1") {
                        setcookie("id", $id, time() + 3600*24*7); // Set for a week.
                    }

                    // Redirect to logged in page.
                    header("Location: todayForm.php");
                }
            }
        }
        
        /*********************************************************************/
        
        // If the Login button was pressed...
        else if (array_key_exists("login", $_POST)) {
            // Check that this username has an account.
            $username = $_POST["username"];
            $username = mysqli_real_escape_string($link, $username);
            $query = "SELECT `id`, `password` FROM `users` WHERE `username` = '".$username."' LIMIT 1";
            $result = mysqli_query($link, $query);
            if (mysqli_num_rows($result) == 0) {
                $msg = "<p>This combination of username and password is invalid.</p>";
            }
            // If so, check that the password is correct.
            else {
                $row = mysqli_fetch_array($result);
                // Hash inputted password.
                $id = $row["id"];
                $hash = md5(md5($id).$_POST["pw"]);
                // If login is successful...
                if ($hash == $row["password"]) {
                    // Set session variable.
                    $_SESSION["id"] = $id;
                    
                    // If requested, set cookie as well.
                    if ($_POST["checkbox"] == "1") {
                        setcookie("id", $id, time() + 3600*24*7); // Set for a week.
                    }
                    
                    // Redirect to logged in page.
                    header("Location: todayForm.php");
                }
                else {
                    $msg = "<p>This combination of username and password is invalid.</p>";
                }
            }
        }
    }
?>

<!-- HTML -------------------------------------------------------------------->

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Mood Keeper - Homepage</title>
    
    <?php include("include/headInclude.php"); ?>

    <link href="https://fonts.googleapis.com/css?family=Tangerine|Lora" rel="stylesheet">
    
<!-- CSS --------------------------------------------------------------------->
    <style type="text/css">
        html { 
            background: url(img/homepage.jpg) no-repeat center center fixed; 
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
        #container {
            width:70%;
            max-width:500px;
            margin:0 auto;
            text-align:center;
            position:relative;
            top:45px;
            border-radius:40px;
            background: rgba(44, 44, 44, 0.4);
            padding:50px;
            margin-bottom:45px;
        }
        h1 {
            font-family:Tangerine !important;
            font-size:60px;
        }
        #login-form {
            display:none;
        }
        a {
            font-weight:bold;
            color:lightblue !important;
        }
        a:hover {
            cursor:pointer;
        }
    </style>
</head>
    
<!-- HTML body --------------------------------------------------------------->
<body>
    <div id="container">
    
    <h1>Mood Keeper</h1>
    <p>Here to help you track your mood.</p><hr>
    
    <!-- Error messages, if any -->
    <div id="msg"><?php if ($msg != "") { echo "<div class='alert alert-danger' role='alert'>".$msg."</div>"; } ?></div>
    
    <!-- Sign up form -->
    <form method="post" id="signup-form">
        <h6>Sign up today! Pick a username and a password.</h6>
        <div class="form-group">
            <input type="text" name="username" class="form-control" placeholder="Pick a username">
        </div>
        <div class="form-group">
            <input type="password" name="pw" class="form-control" placeholder="Password">
        </div>
        <div class="form-group">
            <input type="password" name="pw-conf" class="form-control" placeholder="Confirm your password">
        </div>
        <div class="form-group">
            <input type="hidden" name="checkbox" value="0">
            <input type="checkbox" name="checkbox" class="form-check-input" value="1">Remember me
        </div>
        <div class="form-group">
            <input type="submit" name="signup" class="btn btn-success" value="Sign Up">
        </div>
        <p><a class="toggleButton">Already signed up? Click here to login.</a></p>
    </form>
    
    <!-- Login form -->
    <form method="post" id="login-form">
        <h6>Welcome back! Login with your username and password.</h6>
        <div class="form-group">
            <input type="text" name="username" class="form-control" placeholder="Username">
        </div>
        <div class="form-group">
            <input type="password" name="pw" class="form-control" placeholder="Password">
        </div>
        <div class="form-group">
            <input type="hidden" name="checkbox" value="0">
            <input type="checkbox" name="checkbox" class="form-check-input" value="1">Remember me
        </div>
        <div class="form-group">
            <input type="submit" name="login" class="btn btn-success" value="Login">
        </div>
        <p><a class="toggleButton">Don't have an account? Click here to sign up.</a></p>
    </form>
        
    </div>
    
<!-- JavaScript -------------------------------------------------------------->
    <?php include("include/jsInclude.php"); ?>
    
    <script type="text/javascript">
        // Toggle between sign up and login forms.
        $(".toggleButton").click(function() {
            $("#signup-form").toggle();
            $("#login-form").toggle();
        });
    </script>
</body>
</html>