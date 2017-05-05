<?php
$databaseHost = "localhost";
$databaseUsername = "justin";
$databasePassword = "="; //TODO d
$databaseName = "videodb";

$connection = mysql_connect($databaseHost, $databaseUsername, $databasePassword);

if (!$connection) {
    die('Error: Could not connect for reason "' . mysql_error() . '"!');
}

mysql_select_db($databaseName, $connection);

$newLessonTitle = null;
if (isset($_GET['newLessonTitle'])) {
    $newLessonTitle = $_GET['newLessonTitle'];
} else {
    die('Error: The "newLessonTitle" value was not sent, so I could not insert the new concept to the database!');
}

$newLessonVids = null;
if (isset($_GET['newLessonVids'])) {
    $newLessonVids = explode(",", $_GET['newLessonVids']);
} else {
    die('Error: The "newLessonVids" value was not sent, so I could not insert the new concept to the database!');
}

$newLessonStartSeconds = null;
if (isset($_GET['newLessonStartSeconds'])) {
    $newLessonStartSeconds = explode(",", $_GET['newLessonStartSeconds']);
} else {
    die('Error: The "newLessonStartSeconds" value was not sent, so I could not insert the new concept to the database!');
}

$newLessonEndSeconds = null;
if (isset($_GET['newLessonEndSeconds'])) {
    $newLessonEndSeconds = explode(",", $_GET['newLessonEndSeconds']);
} else {
    die('Error: The "newLessonEndSeconds" value was not sent, so I could not insert the new concept to the database!');
}

$query = "INSERT INTO  `lesson` (`title`) VALUE('" . mysql_real_escape_string($newLessonTitle) . "');";

$result = mysql_query($query);

$query = "SELECT `lid` FROM `lesson` WHERE `title` = '" . mysql_real_escape_string($newLessonTitle) . "';";

$result = mysql_query($query);

$row = mysql_fetch_assoc($result);

$newLessonLid = $row["lid"];

for ($i = 0; $i < count($newLessonVids); $i++) {
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

    $query = "INSERT INTO `lessonVideo` (`lid`, `vid`, `startSeconds`, `endSeconds`) VALUE('" . mysql_real_escape_string($newLessonLid) . "', '" . mysql_real_escape_string($newLessonVids[$i]) . "', '" . mysql_real_escape_string($newLessonStartSeconds[$i]) . "', '" . mysql_real_escape_string($newLessonEndSeconds[$i]) . "');";

    $result = mysql_query($query);
}

mysql_close($connection);

header('Location: index.php');
?>