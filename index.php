<?php
$databaseHost = "localhost";
$databaseUsername = "justin";
$databasePassword = ""; //TODO a
$databaseName = "videodb";

$connection = mysql_connect($databaseHost, $databaseUsername, $databasePassword);

if (!$connection) {
    die("Error: Could not connect for reason \"" . mysql_error() . "\"!");
}

mysql_select_db($databaseName, $connection);

$videoVidArray = array();
$videoYoutubeIdArray = array();
$videoTitleArray = array();
$videoStartSecondsArray = array();
$videoEndSecondsArray = array();
$videoSuggestedQualityArray = array();

$query = "SELECT * FROM `video`";
$result = mysql_query($query);

if ($result) {
    while ($row = mysql_fetch_assoc($result)) {
        $videoVidArray[] = $row["vid"];
        $videoYoutubeIdArray[] = $row["youtubeId"];
        $videoTitleArray[] = $row["title"];
        $videoStartSecondsArray[] = $row["startSeconds"];
        $videoEndSecondsArray[] = $row["endSeconds"];
        $videoSuggestedQualityArray[] = $row["suggestedQuality"];
    }
}

$conceptCidArray = array();
$conceptVidArray = array();
$conceptNameArray = array();
$conceptStartSecondsArray = array();
$conceptEndSecondsArray = array();
$conceptSuggestedQualityArray = array();

$query = "SELECT * FROM `concept`";
$result = mysql_query($query);

if ($result) {
    while ($row = mysql_fetch_assoc($result)) {
        $conceptCidArray[] = $row["cid"];
        $conceptVidArray[] = $row["vid"];
        $conceptNameArray[] = $row["name"];
        $conceptStartSecondsArray[] = $row["startSeconds"];
        $conceptEndSecondsArray[] = $row["endSeconds"];
        $conceptSuggestedQualityArray[] = $row["suggestedQuality"];
    }
}

mysql_close($connection);
?>
<html>
<head>
    <meta charset="UTF-8">
    <title>Project 2</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <link rel="stylesheet" href="main.css"/>
    <style>
        * {
            text-align: center !important;
        }

        .bordered {
            border: 1px solid black
        }

        .padding {
            padding: 10px;
        }

        .margin {
            margin: 10px;
        }

        .max-dimensions {
            width: 10%;
            height: 100%;
        }

        .max-width {
            width: 100%;
        }

        .max-height {
            height: 100%;
        }

        .content {
            margin-top: 50px;
        }

        .video-nav-button {
            width: 100%;
            height: 350px;
        }
    </style>
    <script src="https://www.youtube.com/iframe_api"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
    <script type="text/javascript" src="main.js"></script>
    <script>
        const TRUE = 1;
        const FALSE = 0;
        let player;
        let currentVideoIndex = 0;
        let currentVideoHasStartedPlaying = false;
        let videoList = [
            <?php
            for ($i = 0; $i < count($videoVidArray); $i++) { ?>
            {
                "vid": <?php print $videoVidArray[$i]; ?>,
                "videoId": "<?php print $videoYoutubeIdArray[$i]; ?>",
                "startSeconds": <?php print $videoStartSecondsArray[$i]; ?>,
                "endSeconds": <?php print $videoEndSecondsArray[$i]; ?>,
                "suggestedQuality": "<?php print $videoSuggestedQualityArray[$i]; ?>",
            }<?php if ($i < count($videoVidArray) - 1) print ",";?>
            <?php
            } ?>
        ];
        let conceptList = [
            <?php
            for ($i = 0; $i < count($conceptCidArray); $i++) { ?>
            {
                "vid": "<?php print $conceptVidArray[$i]; ?>",
                "startSeconds": <?php print $conceptStartSecondsArray[$i]; ?>,
                "endSeconds": <?php print $conceptEndSecondsArray[$i]; ?>,
                "suggestedQuality": "<?php print $conceptSuggestedQualityArray[$i]; ?>",
            }<?php if ($i < count($conceptCidArray) - 1) print ",";?>
            <?php
            } ?>
        ];

        function onYouTubeIframeAPIReady() {
            console.log("EYTP: Embedded YouTube Player (EYTP) is initializing...");
            player = new YT.Player("embedded-youtube-player", {
                height: "350",
                width: "520",
                videoId: videoList[currentVideoIndex].videoId,
                playerVars: {
                    "start": videoList[currentVideoIndex].startSeconds,
                    "end": videoList[currentVideoIndex].endSeconds,
                    "disablekb": TRUE,
                    "controls": TRUE,
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
                            console.log($('#autoplay')[0].checked);
                            if ($('#autoplay')[0].checked) {
                                if (currentVideoIndex < videoList.length) {
                                    console.log("EYTP: Starting next video...");
                                    loadVideo(currentVideoIndex);
                                    currentVideoIndex++;
                                    currentVideoHasStartedPlaying = false;
                                } else {
                                    console.log("EYTP: Reached the end of the current videoList.");
                                }
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

        function loadVideo(vid) {
            console.log("Loading video with vid \"" + vid + "\".");
            player.loadVideoById({
                "videoId": videoList[vid].videoId,
                "startSeconds": videoList[vid].startSeconds,
                "endSeconds": videoList[vid].endSeconds,
                "suggestedQuality": videoList[vid].suggestedQuality
            });
        }

        function nextVideo() {
            console.log("\"Next Video\" button clicked.");
            currentVideoIndex++;
            loadVideo(currentVideoIndex);
        }

        function previousVideo() {
            console.log("\"Previous Video\" button clicked.");
            currentVideoIndex--;
            loadVideo(currentVideoIndex);
        }

        function loadConcept(cid) {
            console.error(cid);
            console.log("Loading video with cid \"" + cid + "\".")
            let videoId = getVideoIdOfVideoWithVid(conceptList[cid].vid);
            player.loadVideoById({
                "videoId": videoId,
                "startSeconds": conceptList[cid].startSeconds,
                "endSeconds": conceptList[cid].endSeconds,
                "suggestedQuality": conceptList[cid].suggestedQuality
            })
        }

        function getVideoIdOfVideoWithVid(vid) {
            let videoId = null;
            videoList.forEach((video) => {
                    if (video.vid == vid) {
                        videoId = video.videoId;
                    }
            });

            return videoId;
        }
    </script>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-10 col-md-offset-1 content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-8">
                        <div class="container-fluid bordered">
                            <div class="row">
                                <div class="col-md-12">
                                    <h2>Embedded YouTube Player</h2>
                                </div>
                            </div>
                            <div class="row bordered">
                                <div class="col-md-2 padding">
                                    <button class="btn btn-primary video-nav-button" onclick="previousVideo();">Previous Video</button>
                                </div>
                                <div class="col-md-8 bordered padding">
                                    <div id="embedded-youtube-player">Your browser does not support YouTube's embedded video player (or something went wrong, check the console. In Google Chrome press F12 then click Console).</div>
                                </div>
                                <div class="col-md-2 padding">
                                    <button class="btn btn-primary video-nav-button" onclick="nextVideo();">Next Video</button>
                                </div>
                            </div>
                            <div class="row bordered">
                                <div class="col-md-10 col-md-offset-1 padding">
                                    <span><input type="checkbox" id="autoplay" checked/>Autoplay Next Video</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <button type="button" class="btn btn-primary max-width margin" data-toggle="modal" data-target="#addVideoModal">Add New Video</button>

                        <div id="addVideoModal" class="modal fade" role="dialog">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="./addVideo.php" method="get">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                            <h4 class="modal-title">Video Details</h4>
                                        </div>
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label for="newVideoUrl">YouTube Video URL:</label>
                                                <input type="url" class="form-control" id="newVideoUrl" name="newVideoUrl" placeholder="Enter a valid YouTube URL.">
                                            </div>
                                            <div class="form-group">
                                                <label for="newVideoTitle">Video Title:</label>
                                                <input type="text" class="form-control" id="newVideoTitle" name="newVideoTitle" placeholder="Enter a suitable title for this video.">
                                            </div>
                                            <div class="form-group">
                                                <label for="newVideoSuggestedQuality">Video Quality:</label>
                                                <select class="form-control" id="newVideoSuggestedQuality" name="newVideoSuggestedQuality">
                                                    <option selected>default</option>
                                                    <option>highres</option>
                                                    <option>hd1080</option>
                                                    <option>hd720</option>
                                                    <option>large</option>
                                                    <option>medium</option>
                                                    <option>small </option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <input type="submit" class="btn btn-success" value="Add Video"/>
                                            <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <table class="table table-hover table-bordered margin">
                            <tr>
                                <th colspan="3">Videos</th>
                            </tr>
                            <tr>
                                <th>Title</th>
                                <th colspan="2">Concept</th>
                            </tr>
                            <?php
                            for ($i = 0; $i < count($videoVidArray); $i++) { ?>
                                <tr>
                                    <td>
                                        <button class="btn btn-default" onclick="loadVideo(<?php print ($videoVidArray[$i] - 1); ?>);"><?php print $videoTitleArray[$i]; ?></button>
                                    </td>
                                    <td>
                                        <ul>
                                            <?php for ($j = 0; $j < count($conceptCidArray); $j++) {
                                                if ($conceptVidArray[$j] == $videoVidArray[$i]) { ?>
                                                    <li onclick="loadConcept(<?php print ($conceptCidArray[$j] - 1); ?>)"><?php print $conceptNameArray[$j]; ?></li>
                                                <?php }
                                            } ?>
                                        </ul>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-default" data-toggle="modal" data-target="#addConceptModal<?php print $videoVidArray[$i]; ?>">Add</button>

                                        <div id="addConceptModal<?php print $videoVidArray[$i]; ?>" class="modal fade" role="dialog">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form action="./addConcept.php" method="get">
                                                        <div class="modal-header">
                                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                            <h4 class="modal-title">Concept Details</h4>
                                                        </div>
                                                        <div class="modal-body">
                                                            <input type="hidden" name="newConceptVid" value="<?php print $videoVidArray[$i]; ?>">
                                                            <div class="form-group">
                                                                <label for="videoUrl">YouTube Video URL:</label>
                                                                <input type="url" class="form-control" id="videoUrl" name="videoUrl" value="<?php print "https://www.youtube.com/watch?v=".$videoYoutubeIdArray[$i]; ?>" disabled>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="videoTitle">Video Title:</label>
                                                                <input type="text" class="form-control" id="videoTitle" name="videoTitle" value="<?php print $videoTitleArray[$i]; ?>" disabled>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="newConceptName">Concept Name:</label>
                                                                <input type="text" class="form-control" id="newConceptName" name="newConceptName" placeholder="Enter a name for this concept."/>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="newConceptStartSeconds">Start At:</label>
                                                                <input type="text" class="form-control" id="newConceptStartSeconds" name="newConceptStartSeconds" placeholder="At what point the concept comes into the video."/>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="newConceptEndSeconds">Duration:</label>
                                                                <input type="text" class="form-control" id="newConceptEndSeconds" name="newConceptEndSeconds" placeholder="How long the concept should be for this video."/>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="newConceptSuggestedQuality">Video Quality:</label>
                                                                <select class="form-control" id="newConceptSuggestedQuality" name="newConceptSuggestedQuality">
                                                                    <option selected>default</option>
                                                                    <option>highres</option>
                                                                    <option>hd1080</option>
                                                                    <option>hd720</option>
                                                                    <option>large</option>
                                                                    <option>medium</option>
                                                                    <option>small </option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <input type="submit" class="btn btn-success" value="Add Concept"/>
                                                            <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
