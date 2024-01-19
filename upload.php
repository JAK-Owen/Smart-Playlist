<?php

// Include getID3 library
require '/Applications/XAMPP/xamppfiles/htdocs/TRACK-INFO-VIEWER/getid3/getid3.php';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if files are selected
    if (isset($_FILES['musicFiles'])) {
        $uploadedFiles = $_FILES['musicFiles'];

        // Loop through each uploaded file
        foreach ($uploadedFiles['name'] as $key => $fileName) {
            // GetID3 initialization
            $getID3 = new getID3();

            // Analyze file and extract metadata
            $fileInfo = $getID3->analyze($uploadedFiles['tmp_name'][$key]);
            getid3_lib::CopyTagsToComments($fileInfo);

            // Access metadata
            $title = isset($fileInfo['tags']['id3v2']['title'][0]) ? $fileInfo['tags']['id3v2']['title'][0] : 'N/A';
            $artist = isset($fileInfo['tags']['id3v2']['artist'][0]) ? $fileInfo['tags']['id3v2']['artist'][0] : 'N/A';
            $key = isset($fileInfo['comments']['key']) ? $fileInfo['comments']['key'][0] : 'N/A';
            $bpm = isset($fileInfo['comments']['bpm']) ? $fileInfo['comments']['bpm'][0] : 'N/A';

            // Display metadata
            echo "<h2>File Information:</h2>";
            echo "<p>Title: $title</p>";
            echo "<p>Artist: $artist</p>";
            echo "<p>Key: $key</p>";
            echo "<p>BPM: $bpm</p>";
            echo "<hr>";
        }
    } else {
        echo "Please select music files.";
    }
}

?>
