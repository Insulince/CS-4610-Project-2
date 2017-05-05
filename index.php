<?php
$databaseHost = "localhost";
$databaseUsername = "justin";
$databasePassword = "="; //TODO a
$databaseName = "videodb";

$connection = mysql_connect($databaseHost, $databaseUsername, $databasePassword);

if (!$connection) {
    die("Error: Could not connect for reason \"" . mysql_error() . "\"!");
}

mysql_select_db($databaseName, $connection);

const LVID = 0;
const LID = 1;
const VID = 2;
const START_SECONDS = 3;
const END_SECONDS = 4;

$videoVidArray = array();
$videoYoutubeIdArray = array();
$videoTitleArray = array();
$videoStartSecondsArray = array();
$videoEndSecondsArray = array();
$videoSuggestedQualityArray = array();

$query = "SELECT * FROM `video`;";
$result = mysql_query($query);

if ($result) {
    while ($row = mysql_fetch_assoc($result)) {
        $videoVidArray[] = $row["vid"];
        $videoYoutubeIdArray[] = $row["youtubeId"];
        $videoTitleArray[] = $row["title"];
        $videoSuggestedQualityArray[] = $row["suggestedQuality"];
    }
}

$conceptCidArray = array();
$conceptVidArray = array();
$conceptNameArray = array();
$conceptStartSecondsArray = array();
$conceptEndSecondsArray = array();

$query = "SELECT * FROM `concept`;";
$result = mysql_query($query);

if ($result) {
    while ($row = mysql_fetch_assoc($result)) {
        $conceptCidArray[] = $row["cid"];
        $conceptVidArray[] = $row["vid"];
        $conceptNameArray[] = $row["name"];
        $conceptStartSecondsArray[] = $row["startSeconds"];
        $conceptEndSecondsArray[] = $row["endSeconds"];
    }
}

$lessonLidArray = array();
$lessonTitleArray = array();

$query = "SELECT * FROM `lesson`;";
$result = mysql_query($query);

if ($result) {
    while ($row = mysql_fetch_assoc($result)) {
        $lessonLidArray[] = $row["lid"];
        $lessonTitleArray[] = $row["title"];
    }
}

$lessonVideoLvidArray = array();
$lessonVideoLidArray = array();
$lessonVideoVidArray = array();
$lessonVideoStartSecondsArray = array();
$lessonVideoEndSecondsArray = array();

$query = "SELECT * FROM `lessonVideo`;";
$result = mysql_query($query);

if ($result) {
    while ($row = mysql_fetch_assoc($result)) {
        $lessonVideoLvidArray[] = $row["lvid"];
        $lessonVideoLidArray[] = $row["lid"];
        $lessonVideoVidArray[] = $row["vid"];
        $lessonVideoStartSecondsArray[] = $row["startSeconds"];
        $lessonVideoEndSecondsArray[] = $row["endSeconds"];
    }
}

function videoTitleWithVid($vid, $vidArray, $titleArray)
{
    for ($i = 0; $i < count($vidArray); $i++) {
        if ($vid == $vidArray[$i]) {
            return $titleArray[$i];
        }
    }

    return null;
}

function segmentsInLessonWithLid($lid, $segmentLvidArray, $segmentLidArray, $segmentVidArray, $segmentStartSecondsArray, $segmentEndSecondsArray)
{
    $segmentsInLesson = array();

    for ($i = 0; $i < count($segmentLidArray); $i++) {
        $segmentsInLesson[] = array();
        if ($segmentLidArray[$i] == $lid) {
            $segmentsInLesson[$i][LVID] = $segmentLvidArray[$i];
            $segmentsInLesson[$i][LID] = $segmentLidArray[$i];
            $segmentsInLesson[$i][VID] = $segmentVidArray[$i];
            $segmentsInLesson[$i][START_SECONDS] = $segmentStartSecondsArray[$i];
            $segmentsInLesson[$i][END_SECONDS] = $segmentEndSecondsArray[$i];
        }
    }

    return $segmentsInLesson;
}

mysql_close($connection);
?>
<html>
<head>
    <meta charset="UTF-8">
    <title>Project 2</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
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

        .video-nav-button {
            width: 100%;
            height: calc((((100vw / 12 * 10) / 12 * 8) - 55px) * 0.5625);
        }

        .tab {
            padding: 0;
        }

        .nav-wrapper {
            padding: 0;
        }
    </style>
    <script src="https://www.youtube.com/iframe_api"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
    <script>
        $(document).ready(() => {
            $(".lessons").hide();
        });

        const TRUE = 1;
        const FALSE = 0;
        let player;
        let currentVideoHasStartedPlaying = false;
        let lessonMode = false;
        let lesson;
        let lessonVideoIndex;
        let videoList = [
            <?php
            if (count($videoVidArray) == 0) { ?>
            {
                "vid": 0,
                "videoId": "7Bi8ydyFgcE",
                "startSeconds": 0,
                "endSeconds": 999999999,
                "suggestedQuality": "default"
            }
            <?php }
            for ($i = 0; $i < count($videoVidArray); $i++) { ?>
            {
                "vid": <?php print $videoVidArray[$i]; ?>,
                "videoId": "<?php print $videoYoutubeIdArray[$i]; ?>",
                "startSeconds": 0,
                "endSeconds": 999999999,
                "suggestedQuality": "<?php print $videoSuggestedQualityArray[$i]; ?>"
            }<?php if ($i < count($videoVidArray) - 1) print ",";
            }?>
        ];
        let conceptList = [
            <?php
            for ($i = 0; $i < count($conceptCidArray); $i++) { ?>
            {
                "vid": <?php print $conceptVidArray[$i]; ?>,
                "startSeconds": <?php print $conceptStartSecondsArray[$i]; ?>,
                "endSeconds": <?php print $conceptEndSecondsArray[$i]; ?>,
                "suggestedQuality": getVideoObjectOfVideoWithVid(<?php print $conceptVidArray[$i]; ?>).suggestedQuality
            }<?php if ($i < count($conceptCidArray) - 1) print ",";
            } ?>
        ];
        let lessonList = [
            <?php
            for ($i = 0; $i < count($lessonLidArray); $i++) { ?>
            [
                <?php $segmentsInLesson = segmentsInLessonWithLid($lessonLidArray[$i], $lessonVideoLvidArray, $lessonVideoLidArray, $lessonVideoVidArray, $lessonVideoStartSecondsArray, $lessonVideoEndSecondsArray);
                for ($j = 0; $j < count($segmentsInLesson); $j++) {
                if (isset($segmentsInLesson[$j][VID])) {?>
                {
                    "vid": <?php print $segmentsInLesson[$j][VID]; ?>,
                    "startSeconds": <?php print $segmentsInLesson[$j][START_SECONDS]; ?>,
                    "endSeconds": <?php print $segmentsInLesson[$j][END_SECONDS]; ?>,
                    "suggestedQuality": getVideoObjectOfVideoWithVid(<?php print $segmentsInLesson[$j][VID]; ?>).suggestedQuality
                }<?php if ($j < count($segmentsInLesson) - 1) print ",";
                }
                }?>
            ]<?php if ($i < count($lessonLidArray) - 1) print ",";
            } ?>
        ];
        let segmentList = [
            <?php
            for ($i = 0; $i < count($lessonVideoLvidArray); $i++) { ?>
            {
                "vid": <?php print $lessonVideoVidArray[$i]; ?>,
                "startSeconds": <?php print $lessonVideoStartSecondsArray[$i]; ?>,
                "endSeconds": <?php print $lessonVideoEndSecondsArray[$i]; ?>,
                "suggestedQuality": getVideoObjectOfVideoWithVid(<?php print $lessonVideoVidArray[$i] ?>).suggestedQuality
            }<?php if ($i < count($lessonVideoLvidArray) - 1) print ",";?>
            <?php
            } ?>
        ];

        function onYouTubeIframeAPIReady() {
            console.log("EYTP: Embedded YouTube Player (EYTP) is initializing...");
            player = new YT.Player("embedded-youtube-player", {
                height: ((((window.innerWidth / 12 * 10) / 12 * 12) / 12 * 8) - 55) * 0.5625,
                width: (((window.innerWidth / 12 * 10) / 12 * 12) / 12 * 8) - 55,
                videoId: videoList[0].videoId,
                playerVars: {
                    "start": 0,
                    "end": 999999999,
                    "disablekb": TRUE,
                    "controls": TRUE,
                    "rel": FALSE,
                    "showinfo": FALSE,
                    "modestbranding": TRUE
                },
                events: {
                    "onReady": (event) => {
                        console.log("EYTP: EYTP is ready, starting first video...");
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
                            currentVideoHasStartedPlaying = false;
                            if (lessonMode == true) {
                                nextLessonVideo();
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
            console.log("Loading Video with vid \"" + (vid + 1) + "\".");
            player.loadVideoById({
                "videoId": videoList[vid].videoId,
                "startSeconds": videoList[vid].startSeconds,
                "endSeconds": videoList[vid].endSeconds,
                "suggestedQuality": videoList[vid].suggestedQuality
            });
            exitLessonMode();
        }

        function loadConcept(cid) {
            console.log("Loading Concept with cid \"" + (cid + 1) + "\".");
            player.loadVideoById({
                "videoId": getVideoObjectOfVideoWithVid(conceptList[cid].vid).videoId,
                "startSeconds": conceptList[cid].startSeconds,
                "endSeconds": conceptList[cid].endSeconds,
                "suggestedQuality": conceptList[cid].suggestedQuality
            });
            exitLessonMode();
        }

        function loadLesson(lid) {
            console.log("Loading Lesson with lid \"" + (lid + 1) + "\".");
            lesson = lessonList[lid];
            lessonVideoIndex = 0;
            enterLessonMode();
            loadLessonVideo();
        }

        function loadSegment(lvid) {
            console.log("Loading Segment with lvid \"" + (lvid + 1) + "\".");
            player.loadVideoById({
                "videoId": getVideoObjectOfVideoWithVid(segmentList[lvid].vid).videoId,
                "startSeconds": segmentList[lvid].startSeconds,
                "endSeconds": segmentList[lvid].endSeconds,
                "suggestedQuality": segmentList[lvid].suggestedQuality,
            });
            exitLessonMode();
        }

        function loadLessonVideo() {
            console.log("Loading lesson video...");
            if (lessonVideoIndex >= 0 && lessonVideoIndex < lesson.length) {
                player.loadVideoById({
                    "videoId": getVideoObjectOfVideoWithVid(lesson[lessonVideoIndex].vid).videoId,
                    "startSeconds": lesson[lessonVideoIndex].startSeconds,
                    "endSeconds": lesson[lessonVideoIndex].endSeconds,
                    "suggestedQuality": lesson[lessonVideoIndex].suggestedQuality
                });
            } else {
                console.log("Can't load this lesson video because it is out of range! This is unusual behavior! (Or this is a blank lesson!)");
            }
        }

        function enterLessonMode() {
            lessonMode = true;

            $(".previous-video-button").prop("disabled", true);
            if (lesson.length > 1) {
                $(".next-video-button").prop("disabled", false);
            } else {
                $(".next-video-button").prop("disabled", true);
            }
        }

        function exitLessonMode() {
            lessonMode = false;

            $(".previous-video-button").prop("disabled", true);
            $(".next-video-button").prop("disabled", true);
        }

        function nextLessonVideo() {
            console.log("Loading next video...");
            $(".previous-video-button").prop("disabled", false);
            if (lessonVideoIndex < lesson.length - 1) {
                lessonVideoIndex++;
                loadLessonVideo();
                if (lessonVideoIndex == lesson.length - 1) {
                    $(".next-video-button").prop("disabled", true);
                }
            } else {
                console.log("Lesson complete.");
                exitLessonMode();
                $(".next-video-button").prop("disabled", true);
            }
        }

        function nextVideoPressed() {
            console.log("Loading next video...");
            $(".previous-video-button").prop("disabled", false);
            if (lessonVideoIndex < lesson.length - 1) {
                lessonVideoIndex++;
                loadLessonVideo();
                if (lessonVideoIndex == lesson.length - 1) {
                    $(".next-video-button").prop("disabled", true);
                }
            } else {
                $(".next-video-button").prop("disabled", true);
            }
        }

        function previousVideoPressed() {
            console.log("Loading previous video...");
            $(".next-video-button").prop("disabled", false);
            if (lessonVideoIndex > 0) {
                lessonVideoIndex--;
                loadLessonVideo();
                if (lessonVideoIndex == 0) {
                    $(".previous-video-button").prop("disabled", true);
                }
            } else {
                $(".previous-video-button").prop("disabled", true);
            }
        }

        function getVideoObjectOfVideoWithVid(vid) {
            let video = null;
            videoList.forEach((currentVideo) => {
                if (currentVideo.vid == vid) {
                    video = currentVideo
                }
            });

            return video;
        }

        function goToLessons() {
            $('.lessons').show();
            $('.videos').hide();

            $('#videos-button').addClass('btn-default');
            $('#lessons-button').addClass('btn-primary');
            $('#videos-button').removeClass('btn-primary');
            $('#lessons-button').removeClass('btn-default');
        }

        function goToVideos() {
            $('.videos').show();
            $('.lessons').hide();

            $('#videos-button').addClass('btn-primary');
            $('#lessons-button').addClass('btn-default');
            $('#videos-button').removeClass('btn-default');
            $('#lessons-button').removeClass('btn-primary');
        }

        function toTop() {
            $("html, body").animate({scrollTop: 0}, 600);
        }

        function compileLessonFormData() {
            let form = document.forms["lesson-form"];
            let vids = [];
            let startSeconds = [];
            let endSeconds = [];

            <?php for ($i = 0; $i < count($videoVidArray); $i++) { ?>
            if (form["<?php print $videoTitleArray[$i]; ?>-include"].checked == true) {
                vids.push(<?php print $videoVidArray[$i]; ?>);
                startSeconds.push(form["<?php print $videoTitleArray[$i]; ?>-start"].value);
                endSeconds.push(form["<?php print $videoTitleArray[$i]; ?>-end"].value);
            }
            <?php
            } ?>

            form["newLessonVids"].value = vids;
            form["newLessonStartSeconds"].value = startSeconds;
            form["newLessonEndSeconds"].value = endSeconds;

            return true;
        }
    </script>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-10 col-md-offset-1 content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="container-fluid bordered">
                            <div class="row">
                                <div class="col-md-12">
                                    <h2>Embedded YouTube Player</h2>
                                </div>
                            </div>
                            <div class="row bordered">
                                <div class="col-md-2 nav-wrapper">
                                    <button class="btn btn-primary video-nav-button previous-video-button" onclick="previousVideoPressed();" disabled>Previous Video</button>
                                </div>
                                <div class="col-md-8 nav-wrapper">
                                    <div id="embedded-youtube-player">Your browser does not support YouTube's embedded video player (or something went wrong, check the console. In Google Chrome press F12 then click Console).</div>
                                </div>
                                <div class="col-md-2 nav-wrapper">
                                    <button class="btn btn-primary video-nav-button next-video-button" onclick="nextVideoPressed();" disabled>Next Video</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="data-wrapper">
                            <div class="tabs">
                                <div class="container-fluid">
                                    <div class="row">
                                        <div class="col-md-6 tab">
                                            <a href="#" id="videos-button" class="btn btn-lg btn-primary max-width" onclick="goToVideos();">Videos & Concepts</a>
                                        </div>
                                        <div class="col-md-6 tab">
                                            <a href="#" id="lessons-button" class="btn btn-lg btn-default max-width" onclick="goToLessons();">Lessons</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="videos">
                                <button type="button" class="btn btn-success btn-lg max-width" data-toggle="modal" data-target="#addVideoModal">Add New Video</button>

                                <div id="addVideoModal" class="modal fade" role="dialog">
                                    <div class="modal-dialog modal-lg">
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
                                                        <input type="text" class="form-control" id="newVideoTitle" name="newVideoTitle" placeholder="Enter a suitable title for this Video.">
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
                                                            <option>small</option>
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

                                <table class="table table-hover table-bordered">
                                    <tr>
                                        <th colspan="3">Videos</th>
                                    </tr>
                                    <tr>
                                        <th>Title (Click to view)</th>
                                        <th>Concepts (Click to view)</th>
                                        <th>Add New Concept</th>
                                    </tr>
                                    <?php
                                    for ($i = 0; $i < count($videoVidArray); $i++) { ?>
                                        <tr>
                                            <td>
                                                <button class="btn btn-primary" onclick="loadVideo(<?php print ($videoVidArray[$i] - 1); ?>); toTop();"><?php print $videoTitleArray[$i]; ?></button>
                                            </td>
                                            <td>
                                                <?php for ($j = 0; $j < count($conceptCidArray); $j++) {
                                                    if ($conceptVidArray[$j] == $videoVidArray[$i]) { ?>
                                                        <button class="btn btn-info btn-xs" onclick="loadConcept(<?php print ($conceptCidArray[$j] - 1); ?>); toTop();"><?php print $conceptNameArray[$j] . " (" . ($conceptEndSecondsArray[$j] - $conceptStartSecondsArray[$j]) . "s)"; ?></button>
                                                    <?php }
                                                } ?>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#addConceptModal<?php print $videoVidArray[$i]; ?>">Add</button>

                                                <div id="addConceptModal<?php print $videoVidArray[$i]; ?>" class="modal fade" role="dialog">
                                                    <div class="modal-dialog modal-lg">
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
                                                                        <input type="url" class="form-control" id="videoUrl" name="videoUrl" value="<?php print "https://www.youtube.com/watch?v=" . $videoYoutubeIdArray[$i]; ?>" disabled>
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
                            <div class="lessons">
                                <button type="button" class="btn btn-warning btn-lg max-width" data-toggle="modal" data-target="#addLessonModal">Add New Lesson</button>

                                <div id="addLessonModal" class="modal fade" role="dialog">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <form action="./addLesson.php" method="get" name="lesson-form" onsubmit="return compileLessonFormData();">
                                                <input type="hidden" name="newLessonVids" id="newLessonVids" value=""/>
                                                <input type="hidden" name="newLessonStartSeconds" id="newLessonStartSeconds" value=""/>
                                                <input type="hidden" name="newLessonEndSeconds" id="newLessonEndSeconds" value=""/>
                                                <div class="modal-header">
                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                    <h4 class="modal-title">Lesson Details</h4>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label for="newLessonTitle">Lesson Title:</label>
                                                        <input type="text" class="form-control" id="newLessonTitle" name="newLessonTitle" placeholder="Enter a suitable title for this Lesson.">
                                                    </div>
                                                    <div class="container-fluid">
                                                        <?php
                                                        for ($i = 0; $i < count($videoVidArray); $i++) { ?>
                                                            <div class="row">
                                                                <h4><?php print $videoTitleArray[$i]; ?></h4>
                                                                <div class="col-md-1">
                                                                    <div class="form-group">
                                                                        <label for="<?php print $videoTitleArray[$i]; ?>-include">Use:</label>
                                                                        <input type="checkbox" class="form-control" id="<?php print $videoTitleArray[$i]; ?>-include" name="<?php print $videoTitleArray[$i]; ?>-include"/>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-11">
                                                                    <div class="container-fluid">
                                                                        <div class="row">
                                                                            <div class="col-md-6">
                                                                                <div class="form-group">
                                                                                    <label for="<?php print $videoTitleArray[$i]; ?>-start">Start At:</label>
                                                                                    <input type="text" class="form-control" id="<?php print $videoTitleArray[$i]; ?>-start" name="<?php print $videoTitleArray[$i]; ?>-start" placeholder="At what point this video should start."/>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-6">
                                                                                <div class="form-group">
                                                                                    <label for="<?php print $videoTitleArray[$i]; ?>-end">Duration:</label>
                                                                                    <input type="text" class="form-control" id="<?php print $videoTitleArray[$i]; ?>-end" name="<?php print $videoTitleArray[$i]; ?>-end" placeholder="How long this video is relevant to the lesson."/>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <?php
                                                        } ?>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <input type="submit" class="btn btn-success" value="Add Lesson"/>
                                                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <table class="table table-hover table-bordered">
                                    <tr>
                                        <th colspan="2">Lessons</th>
                                    </tr>
                                    <tr>
                                        <th>Title (Click to start)</th>
                                        <th>Segments (Click to view)</th>
                                    </tr>
                                    <?php for ($i = 0; $i < count($lessonLidArray); $i++) { ?>
                                        <tr>
                                            <td>
                                                <button class="btn btn-warning" onclick="loadLesson(<?php print ($lessonLidArray[$i] - 1); ?>);"><?php print $lessonTitleArray[$i]; ?></button>
                                            </td>
                                            <td>
                                                <?php for ($j = 0; $j < count($lessonVideoLvidArray); $j++) {
                                                    if ($lessonVideoLidArray[$j] == $lessonLidArray[$i]) { ?>
                                                        <button class="btn btn-info btn-xs" onclick="loadSegment(<?php print ($lessonVideoLvidArray[$j] - 1); ?>);"><?php print videoTitleWithVid($lessonVideoVidArray[$j], $videoVidArray, $videoTitleArray) . " (" . ($lessonVideoEndSecondsArray[$j] - $lessonVideoStartSecondsArray[$j]) . "s)"; ?></button>
                                                        <?php
                                                    }
                                                } ?>
                                            </td>
                                        </tr>
                                        <?php
                                    } ?>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
