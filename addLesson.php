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

//Check for "newLessonTitle" parameter.
$newLessonTitle = null;
if (isset($_GET['newLessonTitle'])) {
    $newLessonTitle = $_GET['newLessonTitle'];
} else {
    die('Error: The "newLessonTitle" value was not sent, so I could not insert the new concept to the database!');
}

//Check for "newLessonVids" parameter.
$newLessonVids = null;
if (isset($_GET['newLessonVids'])) {
    $newLessonVids = explode(",", $_GET['newLessonVids']); //We explode here because the details are passed as a string representation of an array of vids, which are comma-delimited, so "exploding" them restores this back to an array which we can use. The same is true for startSeconds and endSeconds
} else {
    die('Error: The "newLessonVids" value was not sent, so I could not insert the new concept to the database!');
}

//Check for "newLessonStartSeconds" parameter.
$newLessonStartSeconds = null;
if (isset($_GET['newLessonStartSeconds'])) {
    $newLessonStartSeconds = explode(",", $_GET['newLessonStartSeconds']);
} else {
    die('Error: The "newLessonStartSeconds" value was not sent, so I could not insert the new concept to the database!');
}

//Check for "newLessonEndSeconds" parameter.
$newLessonEndSeconds = null;
if (isset($_GET['newLessonEndSeconds'])) {
    $newLessonEndSeconds = explode(",", $_GET['newLessonEndSeconds']);
} else {
    die('Error: The "newLessonEndSeconds" value was not sent, so I could not insert the new concept to the database!');
}

//Insert the new lesson into the database.
$query = "INSERT INTO  `lesson` (`title`) VALUE('" . mysql_real_escape_string($newLessonTitle) . "');";
$result = mysql_query($query);

//Get the lid of the lesson we just added for our lessonVideos (lid is auto-incremented, so it has to be done in this order. Add it to the database, the database generates the new lid, then fetch the lid of the entry with title = $newLessonTitle).
$query = "SELECT `lid` FROM `lesson` WHERE `title` = '" . mysql_real_escape_string($newLessonTitle) . "';";
$result = mysql_query($query);
$row = mysql_fetch_assoc($result);
$newLessonLid = $row["lid"];

for ($i = 0; $i < count($newLessonVids); $i++) { //For every newLessonVid...
    //Preform the same check for durations vs. timestamps from addConcept.php (see for more details)
    $timeStampFormat = '/^.+(?=:)/';
    preg_match_all($timeStampFormat, $newLessonStartSeconds[$i], $matches, PREG_SET_ORDER, 0);

    if (isset($matches[0][0])) {
        $newLessonStartSeconds[$i] = 60 * intval($matches[0][0]) + intval(substr($newLessonStartSeconds[$i], strlen($newLessonStartSeconds[$i]) - 2));
    }

    unset($matches);
    preg_match_all($timeStampFormat, $newLessonEndSeconds[$i], $matches, PREG_SET_ORDER, 0);

    if (isset($matches[0][0])) {
        $newLessonEndSeconds[$i] = $newLessonEndSeconds[$i] + 60 * intval($matches[0][0]) + intval(substr($newLessonEndSeconds[$i], strlen($newLessonEndSeconds[$i]) - 2));
    } else {
        $newLessonEndSeconds[$i] = $newLessonStartSeconds[$i] + $newLessonEndSeconds[$i];
    }

    //Insert the new lessonVideo (segment) into the database.
    $query = "INSERT INTO `lessonVideo` (`lid`, `vid`, `startSeconds`, `endSeconds`) VALUE('" . mysql_real_escape_string($newLessonLid) . "', '" . mysql_real_escape_string($newLessonVids[$i]) . "', '" . mysql_real_escape_string($newLessonStartSeconds[$i]) . "', '" . mysql_real_escape_string($newLessonEndSeconds[$i]) . "');";
    $result = mysql_query($query);
}

mysql_close($connection);

header('Location: index.php'); //Take us back to the index page.
?>