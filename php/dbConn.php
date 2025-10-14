<?php

    //step 1 - create a connection to the database
    $host = "localhost"; // or 127.0.0.1
    $user = "root";
    $password = ""; // no password
    $database = "rwddgroup7";

    $connection = mysqli_connect($host, $user, $password, $database);

    if (!$connection) {
        die("Connection failed: " . mysqli_connect_error());
    } else {
        echo "Connected successfully <br><br>";
    }
