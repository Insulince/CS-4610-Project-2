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

//Check for "newConceptVid" parameter.
$newConceptVid = null;
if (isset($_GET['newConceptVid'])) {
    $newConceptVid = $_GET['newConceptVid'];
} else {
    die('Error: The "newVideoUrl" value was not sent, so I could not insert the new concept to the database!');
}

//Check for "newConceptName" parameter.
$newConceptName = null;
if (isset($_GET['newConceptName'])) {
    $newConceptName = $_GET['newConceptName'];
} else {
    die('Error: The "newConceptName" value was not sent, so I could not insert the new concept to the database!');
}

//Check for "newConceptStartSeconds" parameter.
$newConceptStartSeconds = null;
if (isset($_GET['newConceptStartSeconds'])) {
    $newConceptStartSeconds = $_GET['newConceptStartSeconds'];
} else {
    die('Error: The "newConceptStartSeconds" value was not sent, so I could not insert the new concept to the database!');
}

//Check for "newConceptEndSeconds" parameter.
$newConceptEndSeconds = null;
if (isset($_GET['newConceptEndSeconds'])) {
    $newConceptEndSeconds = $_GET['newConceptEndSeconds'];
} else {
    die('Error: The "newConceptEndSeconds" value was not sent, so I could not insert the new concept to the database!');
}

//Determine if the user passed a duration or a timestamp for the StartSeconds or EndSeconds parameters using the regular expression: ^.+(?=:)
// ^     Assert this must match from the start of the provided string.
// .     Match anything.
// +     Match the previous unlimited times.
// (     Start first capturing group.
//  ?=   Positive LookAhead. Assert the following must be true in front of our string.
//  :    Matches ":" literally.
// )     Close first capturing group.
//Example: String: "13:28". Matches: "13"
//This allows us to grab how many minutes the user wants as the end mark. We will substring the string for the seconds.
$timeStampFormat = '/^.+(?=:)/';
preg_match_all($timeStampFormat, $newConceptStartSeconds, $matches, PREG_SET_ORDER, 0);

if (isset($matches[0][0])) { //If any matches were found...
    $newConceptStartSeconds = 60 * intval($matches[0][0]) + intval(substr($newConceptStartSeconds, strlen($newConceptStartSeconds) - 2)); //This is a timestamp, not a duration, so set it to 60 * the match found, + the value of the last two characters (the seconds).
}

unset($matches); //Reset this process for the EndSeconds parameter.
preg_match_all($timeStampFormat, $newConceptEndSeconds, $matches, PREG_SET_ORDER, 0);

if (isset($matches[0][0])) { //If any matches were found...
    $newConceptEndSeconds = $newConceptEndSeconds + 60 * intval($matches[0][0]) + intval(substr($newConceptEndSeconds, strlen($newConceptEndSeconds) - 2)); //This is a timestamp, not a duration, so set it to 60 * the match found, + the value of the last two characters (the seconds).
} else { //Otherwise...
    $newConceptEndSeconds = $newConceptStartSeconds + $newConceptEndSeconds; //This is a duration, so add the duration to the StartSeconds parameter.
}

//Insert the new concept into the database.
$query = "INSERT INTO  `concept` (`vid`, `name`, `startSeconds`, `endSeconds`) VALUE('" . mysql_real_escape_string($newConceptVid) . "', '" . mysql_real_escape_string($newConceptName) . "', '" . mysql_real_escape_string($newConceptStartSeconds) . "', '" . mysql_real_escape_string($newConceptEndSeconds) . "');";
$result = mysql_query($query);

mysql_close($connection);

header('Location: index.php'); //Take us back to the index page.
?>