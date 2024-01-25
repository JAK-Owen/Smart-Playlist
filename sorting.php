<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'database.php';  // Include database.php to access functions

// Function to check if two keys are harmonically compatible
function isCompatible($keyA, $keyB) {
    $wheel = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];

    // Extract numbers and letters from keys
    $matchesA = [];
    $matchesB = [];
    preg_match('/(\d+)([A-B])/', $keyA, $matchesA);
    preg_match('/(\d+)([A-B])/', $keyB, $matchesB);

    $numberA = isset($matchesA[1]) ? (int)$matchesA[1] : null;
    $numberB = isset($matchesB[1]) ? (int)$matchesB[1] : null;
    $letterA = isset($matchesA[2]) ? $matchesA[2] : null;
    $letterB = isset($matchesB[2]) ? $matchesB[2] : null;

    // Check if numbers and letters are set
    if ($numberA !== null && $numberB !== null && $letterA !== null && $letterB !== null) {
        // Check if numbers are the same and letters are the same or consecutive
        return $numberA === $numberB && (abs(array_search($letterA, $wheel) - array_search($letterB, $wheel)) <= 1);
    }

    // Return false if any of the keys is not in the expected format
    return false;
}

// Function to sort tracks based on BPM and Key
function sortByBPMAndKey($tracks) {
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

// Retrieve tracks from the database
$tracks = getTracksFromDatabase();

// Remove duplicate entries by converting array to associative array and back to numeric array
$uniqueTracks = array_values(array_unique($tracks, SORT_REGULAR));

// Sort tracks based on BPM and Key
$sortedTracks = sortByBPMAndKey($uniqueTracks);

// Display the sorted tracks
echo "<html>";
echo "<head>";
echo "<link rel='stylesheet' type='text/css' href='styleSheet.css'>";
echo "</head>";
echo "<body>";

echo "<h2>Sorted Tracks</h2>";
echo "<form method='post' action='refine.php'>";
echo "<input type='submit' name='refinePlaylist' value='Refine Playlist'>";
echo "</form>";
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
?>
