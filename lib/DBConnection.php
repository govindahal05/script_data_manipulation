<?php

/**
 * @return mysqli
 */
function dbConnect($database)
{
    $servername = "localhost";
    $username = "root";
    $password = "GO";
   // echo "\n Connecting to $database \n";
    $conn = mysqli_init();
    $conn = mysqli_connect($servername, $username, $password, $database);
    if ($conn->connect_error) {
        die("\n Connection failed: " . $conn->connect_error);
    }

    return $conn;

}

/**
 * @param $conn
 */
function dbClose($conn)
{
    $conn->close();
}