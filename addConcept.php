<?php
$databaseHost = "localhost";
$databaseUsername = "justin";
$databasePassword = "="; //TODO c
$databaseName = "videodb";

$connection = mysql_connect($databaseHost, $databaseUsername, $databasePassword);

if (!$connection) {
    die('Error: Could not connect for reason "' . mysql_error() . '"!');
}

mysql_select_db($databaseName, $connection);

$newConceptVid = null;
if (isset($_GET['newConceptVid'])) {
    $newConceptVid = $_GET['newConceptVid'];
} else {
    die('Error: The "newVideoUrl" value was not sent, so I could not insert the new concept to the database!');
}

$newConceptName = null;
if (isset($_GET['newConceptName'])) {
    $newConceptName = $_GET['newConceptName'];
} else {
    die('Error: The "newConceptName" value was not sent, so I could not insert the new concept to the database!');
}

$newConceptStartSeconds = null;
if (isset($_GET['newConceptStartSeconds'])) {
    $newConceptStartSeconds = $_GET['newConceptStartSeconds'];
} else {
    die('Error: The "newConceptStartSeconds" value was not sent, so I could not insert the new concept to the database!');
}

$newConceptEndSeconds = null;
if (isset($_GET['newConceptEndSeconds'])) {
    $newConceptEndSeconds = $_GET['newConceptEndSeconds'];
} else {
    die('Error: The "newConceptEndSeconds" value was not sent, so I could not insert the new concept to the database!');
}

$timeStampFormat = '/^.+(?=:)/';
preg_match_all($timeStampFormat, $newConceptStartSeconds, $matches, PREG_SET_ORDER, 0);

if (isset($matches[0][0])) {
    $newConceptStartSeconds = 60 * intval($matches[0][0]) + intval(substr($newConceptStartSeconds, strlen($newConceptStartSeconds) - 2));
}

unset($matches);
preg_match_all($timeStampFormat, $newConceptEndSeconds, $matches, PREG_SET_ORDER, 0);

if (isset($matches[0][0])) {
    $newConceptEndSeconds = $newConceptEndSeconds + 60 * intval($matches[0][0]) + intval(substr($newConceptEndSeconds, strlen($newConceptEndSeconds) - 2));
} else {
    $newConceptEndSeconds = $newConceptStartSeconds + $newConceptEndSeconds;
}

$query = "INSERT INTO  `concept` (`vid`, `name`, `startSeconds`, `endSeconds`) VALUE('$newConceptVid', '$newConceptName', '$newConceptStartSeconds', '$newConceptEndSeconds');";

$result = mysql_query($query);

mysql_close($connection);

header('Location: index.php');
?>