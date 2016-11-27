<!-- Connect to Mood Keeper's database, userinfo, which contains two tables:
users, which contains information about signed up users, and entries, which
contains individual entries of the questionnaire for all users. -->

<?php
    include("databaseInfo.php");
    $link = mysqli_connect(ADDRESS, USERNAME, PASSWORD, DBNAME);
    if (mysqli_connect_error()) {
        die ("Connection to database failed");
    }
?>