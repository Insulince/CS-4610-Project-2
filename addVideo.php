<?php
//Connect to MySql.
$databaseHost = "localhost";
$databaseUsername = "justin";
$databasePassword = "=";
$databaseName = "videodb";

$connection = mysql_connect($databaseHost, $databaseUsername, $databasePassword);

if (!$connection) {
    die('Error: Could not connect for reason "' . mysql_error() . '"!');
}

mysql_select_db($databaseName, $connection);

//Check for "newVideoUrl" parameter.
$newVideoUrl = null;
if (isset($_GET['newVideoUrl'])) {
    $newVideoUrl = $_GET['newVideoUrl'];
} else {
    die('Error: The "newVideoUrl" value was not sent, so I could not insert the new video to the database!');
}

//Check for "newVideoTitle" parameter.
$newVideoTitle = null;
if (isset($_GET['newVideoTitle'])) {
    $newVideoTitle = $_GET['newVideoTitle'];
} else {
    die('Error: The "newVideoTitle" value was not sent, so I could not insert the new video to the database!');
}

//Check for "newVideoSuggestedQuality" parameter.
$newVideoSuggestedQuality = null;
if (isset($_GET['newVideoSuggestedQuality'])) {
    $newVideoSuggestedQuality = $_GET['newVideoSuggestedQuality'];
} else {
    die('Error: The "newVideoSuggestedQuality" value was not sent, so I could not insert the new video to the database!');
}

//Isolate the id portion of the "newVideoUrl" parameter using the regular expression: (?<=watch\?v=).{11}
// (             Start first capturing group.
//  ?<=          Positive LookBehind: Assert the following must be true behind our string:
//   watch\?v=   Matches "watch?v=" literally.
// )             Close first capturing group.
// .             Match anything.
// {11}          Apply the previous statement 11 times.
//This allows us to take a youtube url, and match only the id, because any id has "watch?v=" behind it, and any id is 11 characters long.
$youtubeIdRegularExpressionPattern = '/(?<=watch\?v=).{11}/';
preg_match_all($youtubeIdRegularExpressionPattern, $newVideoUrl, $matches, PREG_SET_ORDER, 0);

$newVideoYoutubeId = null;
if (isset($matches[0][0])) {
    $newVideoYoutubeId = $matches[0][0];
} else {
    die("The provided link is not a valid YouTube URL!");
}

//Check if the provided YouTube Id is unique to the database, alert the user if its not.
$query = "SELECT `youtubeId` FROM `video`;";
$result = mysql_query($query);

$uniqueVideo = true;
if ($result) {
    while ($row = mysql_fetch_assoc($result)) {
        if ($newVideoYoutubeId == $row['youtubeId']) {
            die("Error: Video with URL \"https://www.youtube.com/watch?v=" . $newVideoYoutubeId . "\" already exists, please enter a new video.<br/><button onclick='window.history.back();'>Go Back</button>");
        }
    }
}

//Insert the new video into the database.
$query = "INSERT INTO `video` (`youtubeId`, `title`, `suggestedQuality`) VALUE('" . mysql_real_escape_string($newVideoYoutubeId) . "', '" . mysql_real_escape_string($newVideoTitle) . "', '" . mysql_real_escape_string($newVideoSuggestedQuality) . "');";
$result = mysql_query($query);

mysql_close($connection);

header('Location: index.php'); //Take us back to the index page.
?>