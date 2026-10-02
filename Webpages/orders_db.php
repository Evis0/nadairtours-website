<?php
$servername = "devweb2025.cis.strath.ac.uk";
$username = "gfb23140";
$password = "yoo5Ohtaepha";
$dbname = "gfb23140";

$orders_conn = new mysqli($servername, $username, $password, $dbname);

if ($orders_conn->connect_error) {
    die("Orders DB Connection failed: " . $orders_conn->connect_error);
}

$orders_db_connected = true;
?>
