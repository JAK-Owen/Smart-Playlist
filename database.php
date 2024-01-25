<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'config.php';

// Function to get a database connection
function getDatabaseConnection() {
    global $servername, $username, $password, $dbname;

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    return $conn;
}

// Function to get tracks from the database using prepared statements
function getTracksFromDatabase() {
    $conn = getDatabaseConnection();

    // Query to retrieve tracks from the database
    $sql = "SELECT title, `key`, bpm FROM tracks";
    $result = $conn->query($sql);

    // Check if the query was successful
    if ($result) {
        $tracks = $result->fetch_all(MYSQLI_ASSOC);
        $result->free_result();
        $conn->close();

        return $tracks;
    } else {
        // Log errors to a secure location
        error_log("Error in query: " . $conn->error);
        $conn->close();
        return [];
    }
}
?>
