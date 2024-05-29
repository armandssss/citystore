<?php
$env = parse_ini_file('.env');

$servername = $env["SERVER"];
$username = $env["USERNAME"];
$password = $env["PASSWORD"];
$dbname = $env["DBNAME"];

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>