<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'sorting.php';

// Display the form and sorted tracks
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
