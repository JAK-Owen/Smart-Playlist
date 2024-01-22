<?php

// Include getID3 library
require '/Applications/XAMPP/xamppfiles/htdocs/TRACK-INFO-VIEWER/getid3/getid3.php';

// Include database configuration
require 'config.php';


// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if files are selected
    if (isset($_FILES['musicFiles'])) {
        $uploadedFiles = $_FILES['musicFiles'];

        // Connect to the database
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Check the connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

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

            // Check if 'initial_key' is an array and concatenate values
            $keyArray = isset($fileInfo['tags']['id3v2']['initial_key']) ? $fileInfo['tags']['id3v2']['initial_key'] : [];
            $key = is_array($keyArray) ? implode(', ', $keyArray) : 'N/A';

            $bpm = isset($fileInfo['comments']['bpm']) ? $fileInfo['comments']['bpm'][0] : 'N/A';

            // Insert metadata into the database
            $sql = "INSERT INTO tracks (title, artist, `key`, bpm) VALUES ('$title', '$artist', '$key', '$bpm')";
            $result = $conn->query($sql);

            // Display metadata
            echo "<h2>File Information:</h2>";
            echo "<p>Title: $title</p>";
            echo "<p>Artist: $artist</p>";
            echo "<p>Key: $key</p>";
            echo "<p>BPM: $bpm</p>";
            echo "<hr>";
        }

        // Close the database connection
        $conn->close();
    } else {
        echo "Please select music files.";
    }
}

?>
