<?php
$databaseHost = "localhost";
$databaseUsername = "justin";
$databasePassword = "="; //TODO b
$databaseName = "videodb";

$connection = mysql_connect($databaseHost, $databaseUsername, $databasePassword);

if (!$connection) {
    die('Error: Could not connect for reason "' . mysql_error() . '"!');
}

mysql_select_db($databaseName, $connection);

$newVideoUrl = null;
if (isset($_GET['newVideoUrl'])) {
    $newVideoUrl = $_GET['newVideoUrl'];
} else {
    die('Error: The "newVideoUrl" value was not sent, so I could not insert the new video to the database!');
}

$newVideoTitle = null;
if (isset($_GET['newVideoTitle'])) {
    $newVideoTitle = $_GET['newVideoTitle'];
} else {
    die('Error: The "newVideoTitle" value was not sent, so I could not insert the new video to the database!');
}

$newVideoSuggestedQuality = null;
if (isset($_GET['newVideoSuggestedQuality'])) {
    $newVideoSuggestedQuality = $_GET['newVideoSuggestedQuality'];
} else {
    die('Error: The "newVideoSuggestedQuality" value was not sent, so I could not insert the new video to the database!');
}

$youtubeIdRegularExpressionPattern = '/(?<=watch\?v=).{11}/';
preg_match_all($youtubeIdRegularExpressionPattern, $newVideoUrl, $matches, PREG_SET_ORDER, 0);

$newVideoYoutubeId = null;
if (isset($matches[0][0])) {
    $newVideoYoutubeId = $matches[0][0];
} else {
    die("The provided link is not a valid YouTube URL!");
}

$query = "SELECT `youtubeId` FROM `video`;";

$result = mysql_query($query);

$uniqueVideo = true;
if ($result) {
    while ($row = mysql_fetch_assoc($result)) {
        if ($newVideoYoutubeId == $row['youtubeId']) {
            die("Error: Video with URL \"https://www.youtube.com/watch?v=".$newVideoYoutubeId."\" already exists, please enter a new video.<br/><button onclick='window.history.back();'>Go Back</button>");
        }
    }
}

$query = "INSERT INTO `video` (`youtubeId`, `title`, `suggestedQuality`) VALUE('$newVideoYoutubeId', '$newVideoTitle', '$newVideoSuggestedQuality');";

$result = mysql_query($query);

mysql_close($connection);

header('Location: index.php');
?>