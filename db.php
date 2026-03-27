<?php
// Database connection settings
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "student_course_hub";

// Connect to MySQL
$conn = mysqli_connect($host, $user, $pass, $dbname);

// Check if connection worked
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
