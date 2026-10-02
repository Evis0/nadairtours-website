<?php
$servername = "devweb2025.cis.strath.ac.uk";
$username   = "gfb23140";
$password   = "yoo5Ohtaepha";
$dbname     = "gfb23140";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
