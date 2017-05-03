<?php
$databaseHost = "localhost";
$databaseUsername = "justin";
$databasePassword = ""; //TODO c
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
    $newConceptEndSeconds = $newConceptStartSeconds + $_GET['newConceptEndSeconds'];
} else {
    die('Error: The "newConceptEndSeconds" value was not sent, so I could not insert the new concept to the database!');
}

$newConceptSuggestedQuality = null;
if (isset($_GET['newConceptSuggestedQuality'])) {
    $newConceptSuggestedQuality = $_GET['newConceptSuggestedQuality'];
} else {
    die('Error: The "newConceptSuggestedQuality" value was not sent, so I could not insert the new concept to the database!');
}

$query = "INSERT INTO  `concept` (`vid`, `name`, `startSeconds`, `endSeconds`, `suggestedQuality`) VALUE('$newConceptVid', '$newConceptName', '$newConceptStartSeconds', '$newConceptEndSeconds', '$newConceptSuggestedQuality');";

$result = mysql_query($query);

mysql_close($connection);

header('Location: index.php');
?>