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
    echo "Updating auto increment and defining primary key for table $table_name\n";


    $column_query = "DESCRIBE $table_name";
    $column_result = $conn->query($column_query);
    $column = $column_result->fetch_array();
    $first_column_name = $column[0];

    foreach ($first_column_name as $column_name) {
        $update_query = "ALTER TABLE $table_name ADD PRIMARY KEY ($column_name)";
    }

    $primary_key_query = "ALTER TABLE $table_name AUTO_INCREMENT";
}


$conn->close();