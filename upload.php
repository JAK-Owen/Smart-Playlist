<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'config.php';
require 'database.php';
require 'getID3/getid3.php';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if files are selected
    if (isset($_FILES['musicFiles'])) {
        // Process the form and handle tracks
        handleForm($_FILES['musicFiles']);
    } else {
        // Display a message if no music files are selected
        echo "Please select music files.";
    }
}

function handleForm($uploadedFiles) {
    // Connect to the database
    $conn = getDatabaseConnection();

    // Array to store track information
    $trackList = [];

    // Loop through each uploaded file
    foreach ($uploadedFiles['name'] as $key => $fileName) {
        // GetID3 initialization
        $getID3 = new getID3();

        // Analyze file and extract metadata
        $fileInfo = $getID3->analyze($uploadedFiles['tmp_name'][$key]);
        getid3_lib::CopyTagsToComments($fileInfo);

        // Access metadata
        $title = isset($fileInfo['tags']['id3v2']['title'][0]) ? $conn->real_escape_string($fileInfo['tags']['id3v2']['title'][0]) : 'N/A';
        $artist = isset($fileInfo['tags']['id3v2']['artist'][0]) ? $conn->real_escape_string($fileInfo['tags']['id3v2']['artist'][0]) : 'N/A';

        $keyArray = isset($fileInfo['tags']['id3v2']['initial_key']) ? $fileInfo['tags']['id3v2']['initial_key'] : [];
        $key = is_array($keyArray) ? implode(', ', $keyArray) : 'N/A';

        $bpm = isset($fileInfo['comments']['bpm']) ? $fileInfo['comments']['bpm'][0] : 'N/A';

        // Insert metadata into the database using prepared statements
        $stmt = $conn->prepare("INSERT INTO tracks (title, artist, `key`, bpm) VALUES (?, ?, ?, ?)");

        // Check if the prepared statement was successful
        if ($stmt) {
            $stmt->bind_param("ssss", $title, $artist, $key, $bpm);
            $stmt->execute();
            $stmt->close();
        } else {
            // Log errors to a secure location
            error_log("Error in prepared statement: " . $conn->error);
        }

        // Add track information to the array
        $trackList[] = [
            'title' => $title,
            'artist' => $artist,
            'key' => $key,
            'bpm' => $bpm,
        ];
    }

    // Close the database connection
    $conn->close();

    // Redirect to the sorting page
    header("Location: sorting.php");
    exit();
}
?>
