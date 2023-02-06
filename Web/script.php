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

    //get the first column of each table
    $result2 = $conn->query($query);
    $row = $result2->fetch_array();
    $first_column = $row[0];
    // set the first column of each table to primary key
    // $query = "ALTER TABLE $table_name ADD PRIMARY KEY ($first_column)";
    // //when done echo success
    // echo "Success";
    echo $first_column;
}


$conn->close();