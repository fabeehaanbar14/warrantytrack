<?php
// Database connection settings.
// Update these if your local MySQL setup differs (XAMPP default shown here).
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "warrantytrack_db";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
