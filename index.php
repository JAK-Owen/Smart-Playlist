<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel='stylesheet' type='text/css' href='styleSheet.css'>
    <title>Smart Playlist</title>
</head>
<body>

    <h2>Upload Music Files</h2>
    
    <form action="upload.php" method="post" enctype="multipart/form-data">
        <label for="musicFiles">Select Tracks:</label>
        <input type="file" name="musicFiles[]" id="musicFiles" accept=".mp3, .wav, .ogg" multiple required>
        <br>
        <input type="submit" value="Upload">
    </form>

</body>
</html>
