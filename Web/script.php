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

// Loop through each table
while ($table = $result->fetch_array()) {
    $table_name = $table[0];
    echo "Updating auto increment and defining primary key for table $table_name\n";

    // Query to get the name of the first column in the table
    $column_query = "DESCRIBE $table_name";
    $column_result = $conn->query($column_query);
    $column = $column_result->fetch_array();
    $first_column_name = $column[0];
    //fist colunn last id


    echo json_encode(
        $table_name
    );
}

$conn->close();