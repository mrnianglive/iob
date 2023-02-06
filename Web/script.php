<?php
$servername = "localhost";
$username = "root";
$password = "2023Loly@@";
$dbname = "iob";
// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to get the names of all tables in the database
$query = "SHOW TABLES";
$result = $conn->query($query);

$table_names = array();
// Loop through each table
while ($table = $result->fetch_array()) {
    $table_name = $table[0];
    //Get first column of each table
    $query = "ALTER TABLE $table_name AUTO_INCREMENT";
}


$conn->close();