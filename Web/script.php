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


    // Query to update the auto increment for the table
    $update_query = "ALTER TABLE $table_name AUTO_INCREMENT = $first_column_name";
    if ($conn->query($update_query) === TRUE) {
        echo "Auto increment for table $table_name updated successfully\n";
    } else {
        echo "Error updating auto increment for table $table_name: " . $conn->error . "\n";
    }

    // Query to define the primary key for the table
    $pk_query = "ALTER TABLE $table_name ADD PRIMARY KEY ($first_column_name)";
    if ($conn->query($pk_query) === TRUE) {
        echo "Primary key for table $table_name defined successfully\n";
    } else {
        echo "Error defining primary key for table $table_name: " . $conn->error . "\n";
    }
}

$conn->close();