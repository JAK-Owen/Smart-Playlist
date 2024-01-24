<?php

// Include getID3 library
require '/Applications/XAMPP/xamppfiles/htdocs/TRACK-INFO-VIEWER/getid3/getid3.php';

// Include database configuration
require 'config.php';

// Function to get musical key notation
function getMusicalKey($camelotKey)
{
    $keyMap = [
        '1A' => 'Ab Minor', '2A' => 'Eb Minor', '3A' => 'Bb Minor', '4A' => 'F Minor', '5A' => 'C Minor', '6A' => 'G Minor',
        '7A' => 'D Minor', '8A' => 'A Minor', '9A' => 'E Minor', '10A' => 'B Minor', '11A' => 'F# Minor', '12A' => 'Db Minor',
        '1B' => 'B Major', '2B' => 'F# Major', '3B' => 'Db Major', '4B' => 'Ab Major', '5B' => 'Eb Major', '6B' => 'Bb Major',
        '7B' => 'F Major', '8B' => 'C Major', '9B' => 'G Major', '10B' => 'D Major', '11B' => 'A Major', '12B' => 'E Major',
    ];

    return $keyMap[$camelotKey] ?? $camelotKey;
}

// Function to sort tracks by BPM and Key based on Camelot Wheel
function sortByBPMAndKey($tracks)
{
    // Sort by BPM in descending order
    usort($tracks, function ($a, $b) {
        return $b['bpm'] <=> $a['bpm'];
    });

    // Define a flag to check if a break should be added
    $addBreak = false;

    // Sort by Key
    usort($tracks, function ($a, $b) use (&$addBreak) {
        $bpmDiff = $b['bpm'] - $a['bpm'];

        // If BPM values are equal, check for harmonic compatibility
        if ($bpmDiff === 0 && !isCompatible($a['key'], $b['key'])) {
            $addBreak = true;
        }

        // If BPM values are equal, return the result of the key comparison
        return $bpmDiff ?: $a['key'] <=> $b['key'];
    });

    // Insert an empty row if a break is detected
    if ($addBreak) {
        $tracks[] = ['title' => '', 'key' => '', 'bpm' => ''];
    }

    return $tracks;
}

// Function to check if two keys are harmonically compatible
function isCompatible($keyA, $keyB)
{
    $wheel = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];

    // Extract numbers and letters from keys
    preg_match('/(\d+)([A-B])/', $keyA, $matchesA);
    preg_match('/(\d+)([A-B])/', $keyB, $matchesB);

    $numberA = (int)$matchesA[1];
    $numberB = (int)$matchesB[1];
    $letterA = $matchesA[2];
    $letterB = $matchesB[2];

    // Check if numbers are the same and letters are the same or consecutive
    return $numberA === $numberB && (abs(array_search($letterA, $wheel) - array_search($letterB, $wheel)) <= 1);
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if files are selected
    if (isset($_FILES['musicFiles'])) {
        // Get the array of uploaded files
        $uploadedFiles = $_FILES['musicFiles'];

        // Connect to the database
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Check the database connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

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
                // Display an error message if the prepared statement fails
                echo "Error in prepared statement: " . $conn->error;
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

        // Sort tracks based on BPM and Key using the defined function
        $sortedTracks = sortByBPMAndKey($trackList);

        // Display sorted tracks in a table
        echo "<html>";
        echo "<head>";
        echo "<link rel='stylesheet' type='text/css' href='styleSheet.css'>";
        echo "</head>";
        echo "<body>";

        echo "<h2>Sorted Tracks</h2>";
        echo "<table class='playlist-table'>";
        echo "<tr class='header-column'><th>Title</th><th>Key</th><th>BPM</th></tr>";

        foreach ($sortedTracks as $track) {
            echo "<tr>";
            echo "<td class='playlist-column'>{$track['title']}</td>";
            echo "<td class='playlist-column'>{$track['key']}</td>";
            echo "<td class='playlist-column'>{$track['bpm']}</td>";
            echo "</tr>";
        }

        echo "</table>"; 

        echo "</body>";
        echo "</html>";
    } else {
        // Display a message if no music files are selected
        echo "Please select music files.";
    }
}

?>
