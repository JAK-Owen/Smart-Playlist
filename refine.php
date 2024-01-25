<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'sorting.php';

// Refine the playlist based on harmonically compatible keys
$refinedTracks = refinePlaylist($sortedTracks);

// Display refined tracks in a table
echo "<html>";
echo "<head>";
echo "<link rel='stylesheet' type='text/css' href='styleSheet.css'>";
echo "</head>";
echo "<body>";

echo "<h2>Refined Playlist</h2>";
echo "<table class='playlist-table'>";
echo "<tr class='header-column'><th>Title</th><th>Key</th><th>BPM</th></tr>";

foreach ($refinedTracks as $track) {
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
