<?php
$databaseHost = "localhost";
$databaseUsername = "justin";
$databasePassword = ""; //TODO
$databaseName = "videodb";

$connection = mysql_connect($databaseHost, $databaseUsername, $databasePassword);

if (!$connection) {
    die("Error: Could not connect for reason \"" . mysql_error() . "\"!");
}

mysql_select_db($databaseName, $connection);

$vidArray = array();
$titleArray = array();
$descriptionArray = array();
$youtubeIdArray = array();

$query = "SELECT * FROM `video`";
$result = mysql_query($query);

if ($result) {
    while ($row = mysql_fetch_assoc($result)) {
        $vidArray[] = $row["vid"];
        $titleArray[] = $row["title"];
        $descriptionArray[] = $row["description"];
        $youtubeIdArray[] = $row["youtubeId"];
    }
}

$playlist = "[";

for ($youtubeIdIndex = 0; $youtubeIdIndex < count($youtubeIdArray); $youtubeIdIndex++) {
    $playlist .= "\"" . $youtubeIdArray[$youtubeIdIndex] . "\"";

    if ($youtubeIdIndex < count($youtubeIdArray) - 1) {
        $playlist .= ", ";
    }
}

$playlist .= "]";

mysql_close($connection);
?>
<html>
<head>
    <meta charset="UTF-8">
    <title>Project 2</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <link rel="stylesheet" href="main.css"/>
    <script src="https://www.youtube.com/iframe_api"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
    <script type="text/javascript" src="main.js"></script>
    <script>
        const TRUE = 1;
        const FALSE = 0;
        let player;
        let currentVideoIndex = 0;
        let currentVideoHasStartedPlaying = false;
        let playlist = [{
            "videoId": "sGPrx9bjgC8",
            "startSeconds": 10,
            "endSeconds": 15,
            "suggestedQuality": "default"
        }, {
            "videoId": "lzzD1aNsLVU",
            "startSeconds": 10,
            "endSeconds": 15,
            "suggestedQuality": "default"
        }, {
            "videoId": "fDr_7aX0ACE",
            "startSeconds": 10,
            "endSeconds": 15,
            "suggestedQuality": "default"
        }, {
            "videoId": "YBAGjk5b2us",
            "startSeconds": 10,
            "endSeconds": 15,
            "suggestedQuality": "default"
        }, {
            "videoId": "hGHGLlxlvBw",
            "startSeconds": 10,
            "endSeconds": 15,
            "suggestedQuality": "default"
        }, {
            "videoId": "RLsNCKC6sL0",
            "startSeconds": 10,
            "endSeconds": 15,
            "suggestedQuality": "default"
        }, {
            "videoId": "XkN2kMHA0C4",
            "startSeconds": 10,
            "endSeconds": 15,
            "suggestedQuality": "default"
        }, {
            "videoId": "GZS0icVCB80",
            "startSeconds": 10,
            "endSeconds": 15,
            "suggestedQuality": "default"
        }, {
            "videoId": "dir77Z996QE",
            "startSeconds": 10,
            "endSeconds": 15,
            "suggestedQuality": "default"
        }];

        function onYouTubeIframeAPIReady() {
            console.log("EYTP: Embedded YouTube Player (EYTP) is initializing...");
            player = new YT.Player("embedded-youtube-player", {
                height: "390",
                width: "640",
                videoId: playlist[currentVideoIndex].videoId,
                playerVars: {
                    "start": playlist[currentVideoIndex].startSeconds,
                    "end": playlist[currentVideoIndex].endSeconds,
                    "disablekb": TRUE,
                    "controls": FALSE,
                    "rel": FALSE,
                    "showinfo": FALSE,
                    "modestbranding": TRUE
                },
                events: {
                    "onReady": (event) => {
                        console.log("EYTP: EYTP is ready, starting first video...");
                        event.target.playVideo();
                        currentVideoIndex++;
                    },
                    "onStateChange": (event) => {
                        console.log("EYTP: State changed to \"" + interpretState(event.data) + "\".");
                        if (event.data == YT.PlayerState.PLAYING) {
                            if (currentVideoHasStartedPlaying == false) {
                                console.log("EYTP: Video started.");
                                currentVideoHasStartedPlaying = true;
                            }
                        }
                        if (event.data == YT.PlayerState.ENDED && currentVideoHasStartedPlaying == true) {
                            console.log("EYTP: Current video ended.");
                            if (currentVideoIndex < playlist.length) {
                                console.log("EYTP: Starting next video...");
                                event.target.loadVideoById({
                                    "videoId": playlist[currentVideoIndex].videoId,
                                    "startSeconds": playlist[currentVideoIndex].startSeconds,
                                    "endSeconds": playlist[currentVideoIndex].endSeconds,
                                    "suggestedQuality": playlist[currentVideoIndex].suggestedQuality
                                });
                                currentVideoIndex++;
                                currentVideoHasStartedPlaying = false;
                            } else {
                                console.log("EYTP: Reached the end of the current playlist.");
                            }
                        }
                    },
                    "onPlaybackQualityChange": (event) => {
                        console.log("EYTP: Playback quality changed to \"" + event.target.getPlaybackQuality() + "\".");
                    },
                    "onError": (event) => {
                        console.log("EYTP: Error encountered with code \"" + event.data + "\".");
                    }
                }
            });
        }

        function interpretState(state) {
            switch (state) {
                case -1:
                    return "(" + state + ") Unstarted";
                case 0:
                    return "(" + state + ") Ended";
                case 1:
                    return "(" + state + ") Playing";
                case 2:
                    return "(" + state + ") Paused";
                case 3:
                    return "(" + state + ") Buffering";
                case 5:
                    return "(" + state + ") Video Cued";
                default:
                    return "(" + state + ") Unknown State";
            }
        }
    </script>
</head>
<body>
<div id="embedded-youtube-player">Your browser does not support YouTube's embedded video player (or something went wrong, check the console (F12 -> Click Console)).</div>
</body>
</html>
