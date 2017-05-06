<?php
//Connect to MySql.
$databaseHost = "localhost";
$databaseUsername = "justin";
$databasePassword = "=";
$databaseName = "videodb";

$connection = mysql_connect($databaseHost, $databaseUsername, $databasePassword);

if (!$connection) {
    die("Error: Could not connect for reason \"" . mysql_error() . "\"!");
}

mysql_select_db($databaseName, $connection);

const LVID = 0; //LVID is the first element in the segment array (see below).
const LID = 1; //LID is the second element in the segment array.
const VID = 2; //VID is the third element in the segment array.
const START_SECONDS = 3; //StartSeconds is the fourth element in the segment array.
const END_SECONDS = 4; //EndSeconds is the fifth element in the segment array.

//Get data from "video" table.
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

//Get data from "concept" table.
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

//Get data from "lesson" table.
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

//Get data from "lessonVideo" (segment) table.
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

//Returns the title of the video which has the passed vid (or null).
function videoTitleWithVid($vid, $vidArray, $titleArray)
{
    for ($i = 0; $i < count($vidArray); $i++) {
        if ($vid == $vidArray[$i]) {
            return $titleArray[$i];
        }
    }

    return null;
}

//Returns an array of segments (lessonVideo details, this is really just a 2D array) that are associated with the passed lid.
//The reason this is necessary is because for every other table, the objects are mapped to their corresponding id - 1, whereas the lessonVideo objects cannot be mapped this way, because each lesson has a variable amount of videos in it, so the lvid's are all over the place.
//Thus, I have to gather them up myself instead of relying on an inherent ordering via indices. This function does that.
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
    <!--Bootstrap CSS-->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <style>
        * {
            text-align: center !important;
        }

        .max-width {
            width: 100%;
        }

        .btn {
            border-radius: 0;
        }

        .video-nav-button {
            width: 100%;
            height: calc(((((100vw / 12 * 10) / 12 * 12) / 12 * 8) - 50px) * 0.5625);
            /*This value corresponds to the layout of the bootstrap grid I have below in the HTML.
              Divide 100% of the viewport width by 12, then multiply it by 10 for the first container.
              Divide that by 12, then multiply it by 12 for the second container (unnecessary step, arithmetically speaking, but included for consistency).
              Divide that by 12, then multiply it by 8 for the third container.
              Subtract 50px for effect.
              Multiply by the ratio of 9/16 (widescreen) to get the proper height. */
        }

        .content {
            margin-top: 10px;
            margin-bottom: 50px;
        }

        .tabs {
            margin-top: 20px;
        }

        .tab {
            padding: 0;
        }

        .videos, .lessons {
            padding: 20px 20px 0;
            border-left: solid #cccccc 1px;
            border-right: solid #cccccc 1px;
            border-bottom: solid #cccccc 1px;
        }

        .nav-wrapper {
            padding: 0;
        }

        .player {
            margin-top: 10px;
            margin-bottom: 35px;
        }
    </style>
    <!--YouTube API-->
    <script src="https://www.youtube.com/iframe_api"></script>
    <!--JQuery-->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
    <!--Bootstrap JS-->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
    <script>
        $(document).ready(() => { //Once the document loads...
            $(".lessons").hide(); //Hide the lessons content. We start on the videos tab.
        });

        const TRUE = 1; //Helper variable.
        const FALSE = 0; //Helper variable.
        let player; //This will hold the YouTube player.
        let currentVideoHasStartedPlaying = false; //Records the state of the player in connection to the lesson.
        let lessonMode = false; //If we are in a lesson or not.
        let lesson; //Holds the current lesson object (an array of segments).
        let lessonVideoIndex; //Records how far along the lesson we are.
        let videoList = [ //Dump all the videos in this list as javascript objects.
            <?php
            if (count($videoVidArray) == 0) { ?> //If there are no videos in the database yet...
            {
                "vid": 0,
                "videoId": "7Bi8ydyFgcE", //Default the player to a video of black screen for 24 hours, just for fun.
                "startSeconds": 0,
                "endSeconds": 999999999,
                "suggestedQuality": "default"
            }
            <?php }
            for ($i = 0; $i < count($videoVidArray); $i++) { ?> //For every video in the database...
            {
                "vid": <?php print $videoVidArray[$i]; ?>, //Dump the video data here.
                "videoId": "<?php print $videoYoutubeIdArray[$i]; ?>",
                "startSeconds": 0,
                "endSeconds": 999999999,
                "suggestedQuality": "<?php print $videoSuggestedQualityArray[$i]; ?>"
            }<?php if ($i < count($videoVidArray) - 1) print ","; //If this isn't the last video object, add a comma to the end of this object.
            } ?>
        ];
        let conceptList = [ //Dump all the concepts in this list as javascript objects.
            <?php
            for ($i = 0; $i < count($conceptCidArray); $i++) { ?> //For every concept in the database...
            {
                "vid": <?php print $conceptVidArray[$i]; ?>, //Dump the concept data here.
                "startSeconds": <?php print $conceptStartSecondsArray[$i]; ?>,
                "endSeconds": <?php print $conceptEndSecondsArray[$i]; ?>,
                "suggestedQuality": getVideoObjectOfVideoWithVid(<?php print $conceptVidArray[$i]; ?>).suggestedQuality //SuggestedQuality should be the same as in the video list, so fetch the suggested quality of that object.
            }<?php if ($i < count($conceptCidArray) - 1) print ",";
            } ?>
        ];
        let lessonList = [ //Dump all the lesson data in this list as an array of javascript objects.
            <?php
            for ($i = 0; $i < count($lessonLidArray); $i++) { ?> //For every lesson in the database...
            [
                <?php $segmentsInLesson = segmentsInLessonWithLid($lessonLidArray[$i], $lessonVideoLvidArray, $lessonVideoLidArray, $lessonVideoVidArray, $lessonVideoStartSecondsArray, $lessonVideoEndSecondsArray); //Get all the segments associated with this lesson.
                for ($j = 0; $j < count($segmentsInLesson); $j++) { //For every segment associated with this lesson...
                if (isset($segmentsInLesson[$j][VID])) {?> //If there is at least one segment in this lesson...
                {
                    "vid": <?php print $segmentsInLesson[$j][VID]; ?>, //Dump the segment data here.
                    "startSeconds": <?php print $segmentsInLesson[$j][START_SECONDS]; ?>,
                    "endSeconds": <?php print $segmentsInLesson[$j][END_SECONDS]; ?>,
                    "suggestedQuality": getVideoObjectOfVideoWithVid(<?php print $segmentsInLesson[$j][VID]; ?>).suggestedQuality
                }<?php if ($j < count($segmentsInLesson) - 1) print ",";
                }
                } ?>
            ]<?php if ($i < count($lessonLidArray) - 1) print ",";
            } ?>
        ];
        let segmentList = [ //Dump all the segments in this list as javascript objects.
            <?php
            for ($i = 0; $i < count($lessonVideoLvidArray); $i++) { ?> //For every segment in the databse.
            {
                "vid": <?php print $lessonVideoVidArray[$i]; ?>, //Dump the segment data here.
                "startSeconds": <?php print $lessonVideoStartSecondsArray[$i]; ?>,
                "endSeconds": <?php print $lessonVideoEndSecondsArray[$i]; ?>,
                "suggestedQuality": getVideoObjectOfVideoWithVid(<?php print $lessonVideoVidArray[$i] ?>).suggestedQuality
            }<?php if ($i < count($lessonVideoLvidArray) - 1) print ",";
            } ?>
        ];

        //Implemented for the API.
        function onYouTubeIframeAPIReady() {
            console.log("EYTP: Embedded YouTube Player (EYTP) is initializing...");
            player = new YT.Player("embedded-youtube-player", { //Instantiate a new YouTubePlayer object and put it in the div with id "embedded-youtube-player"
                width: (((window.innerWidth / 12 * 10) / 12 * 12) / 12 * 8) - 50, //Give it this width.
                //This corresponds to the layout of the bootstrap grid I have below in the HTML.
                //Take 100% of the screen width for the whole window.
                //Divide it by 12 and multiply it by 10 for the first container.
                //Divide that by 12 and multiply it by 12 for the second container (redundant but consistent).
                //Divide that by 12 and multiply it by 8 for the third container.
                //Subtract 50 pixels for effect and sizing.
                height: ((((window.innerWidth / 12 * 10) / 12 * 12) / 12 * 8) - 50) * 0.5625, //Give it this height.
                //This is just the width * the ratio 9/16 (for widescreen).
                //
                //This gives us a dynamically sized YouTube player which is in the shape of a standard widescreen video. If the player looks out of place, you probably resized your window, you need to refresh to fix it.
                videoId: videoList[0].videoId, //The first video is the first videoId in the videoList array.
                playerVars: { //Customization of the player:
                    "start": 0, //Start this video at 0.
                    "end": 999999999, //End this video at max time.
                    "disablekb": TRUE, //Disable keyboard input.
                    "controls": TRUE, //Allow user to control the video.
                    "rel": FALSE, //Do not display related videos at the end.
                    "showinfo": FALSE, //Do not show information about this video.
                    "modestbranding": TRUE //Minimize YouTube branding.
                },
                events: { //Attach these event handlers...
                    "onReady": (event) => { //When the player is ready...
                        console.log("EYTP: EYTP is ready, starting first video...");
                    },
                    "onStateChange": (event) => { //When the player changes state...
                        console.log("EYTP: State changed to \"" + interpretState(event.data) + "\".");
                        if (event.data == YT.PlayerState.PLAYING) { //If the state is PLAYING...
                            if (currentVideoHasStartedPlaying == false) { //If the current video has not already started playing...
                                console.log("EYTP: Video started.");
                                currentVideoHasStartedPlaying = true; //Then it has started playing now.
                            }
                        }
                        if (event.data == YT.PlayerState.ENDED && currentVideoHasStartedPlaying == true) { //If the state is ENDED, and the current video has already started playing...
                            console.log("EYTP: Current video ended.");
                            currentVideoHasStartedPlaying = false; //This video has finished.
                            if (lessonMode == true) { //If we are in lesson mode...
                                nextLessonVideo(); //Advance to the next lesson video.
                            }
                        }
                    },
                    "onPlaybackQualityChange": (event) => { //When the playback quality changes...
                        console.log("EYTP: Playback quality changed to \"" + event.target.getPlaybackQuality() + "\".");
                    },
                    "onError": (event) => { //When the player encounters an error...
                        console.log("EYTP: Error encountered with code \"" + event.data + "\".");
                    }
                }
            });
        }

        //Makes player states more user friendly.
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

        //Loads a video object into the player.
        function loadVideo(vid) {
            console.log("Loading Video with vid \"" + (vid + 1) + "\".");
            player.loadVideoById({ //Invoke the load function on the player.
                "videoId": videoList[vid].videoId, //Dump the video object data here.
                "startSeconds": videoList[vid].startSeconds,
                "endSeconds": videoList[vid].endSeconds,
                "suggestedQuality": videoList[vid].suggestedQuality
            });
            exitLessonMode();
        }

        //Loads a concept object into the player.
        function loadConcept(cid) {
            console.log("Loading Concept with cid \"" + (cid + 1) + "\".");
            player.loadVideoById({
                "videoId": getVideoObjectOfVideoWithVid(conceptList[cid].vid).videoId, //videoId's are only stored in video objects, so we have to fetch the videoId via the vid we have access to here.
                "startSeconds": conceptList[cid].startSeconds, //Dump the concept object data here.
                "endSeconds": conceptList[cid].endSeconds,
                "suggestedQuality": conceptList[cid].suggestedQuality
            });
            exitLessonMode();
        }

        //Sets up the page for a lesson.
        function loadLesson(lid) {
            console.log("Loading Lesson with lid \"" + (lid + 1) + "\".");
            lesson = lessonList[lid]; //Record the lesson we are on.
            lessonVideoIndex = 0; //Reset the lesson video index.
            enterLessonMode(); //Enter lesson mode.
            loadLessonVideo(); //Load the first video.
        }

        //Loads a segment object into the player.
        function loadSegment(lvid) {
            console.log("Loading Segment with lvid \"" + (lvid + 1) + "\".");
            player.loadVideoById({
                "videoId": getVideoObjectOfVideoWithVid(segmentList[lvid].vid).videoId,
                "startSeconds": segmentList[lvid].startSeconds, //Dump the segment object data here.
                "endSeconds": segmentList[lvid].endSeconds,
                "suggestedQuality": segmentList[lvid].suggestedQuality,
            });
            exitLessonMode();
        }

        //Loads a segment object (lesson video) into the player.
        function loadLessonVideo() {
            console.log("Loading lesson video...");
            if (lessonVideoIndex >= 0 && lessonVideoIndex < lesson.length) { //If we are in range of the lesson videos for this lesson...
                player.loadVideoById({ //Load the next lesson video.
                    "videoId": getVideoObjectOfVideoWithVid(lesson[lessonVideoIndex].vid).videoId, //Dump the segment data here.
                    "startSeconds": lesson[lessonVideoIndex].startSeconds,
                    "endSeconds": lesson[lessonVideoIndex].endSeconds,
                    "suggestedQuality": lesson[lessonVideoIndex].suggestedQuality
                });
            } else { //If we are out of range of the lesson videos for this lesson...
                console.log("Can't load this lesson video because it is out of range! This is unusual behavior! (Or this is a blank lesson!)");
                exitLessonMode();
            }
        }

        //Prepares us for lesson mode.
        function enterLessonMode() {
            lessonMode = true; //We are in lesson mode.

            $(".previous-video-button").prop("disabled", true); //Turn off the previous button.
            if (lesson.length > 1) { //If there is more than 1 video in this lesson...
                $(".next-video-button").prop("disabled", false); //Turn on the next button.
            } else { //Otherwise...
                $(".next-video-button").prop("disabled", true); //Turn off the next button.
            }
        }

        //Prepares us for non-lesson mode.
        function exitLessonMode() {
            lessonMode = false; //We are not in lesson mode.

            $(".previous-video-button").prop("disabled", true); //Turn off the buttons.
            $(".next-video-button").prop("disabled", true);
        }

        //Loads the next lesson video via automatic ending of the previous video.
        function nextLessonVideo() {
            console.log("Loading next video...");
            $(".previous-video-button").prop("disabled", false); //We are advancing, so there must be a previous video, so enable the previous button.
            if (lessonVideoIndex < lesson.length - 1) { //If there is another video in this lesson...
                lessonVideoIndex++; //Advance to it.
                loadLessonVideo(); //Load it.
                if (lessonVideoIndex == lesson.length - 1) { //If this is the last video of the lesson...
                    $(".next-video-button").prop("disabled", true); //Disable the next button.
                }
            } else { //If there is not another video in this lesson...
                console.log("Lesson complete.");
                exitLessonMode(); //Exit lesson mode.
                $(".next-video-button").prop("disabled", true); //Turn off the next button.
            }
        }

        //Loads the next lesson video via the next button.
        function nextVideoPressed() {
            console.log("Loading next video...");
            $(".previous-video-button").prop("disabled", false); //We are advancing, so there must be a previous video, so enable the previous button.
            if (lessonVideoIndex < lesson.length - 1) { //If there is another video in this lesson...
                lessonVideoIndex++; //Advance to it.
                loadLessonVideo(); //Load it.
                if (lessonVideoIndex == lesson.length - 1) { //If this is the last video of the lesson...
                    $(".next-video-button").prop("disabled", true); //Disable the next button.
                }
            } else { //If there is not another video in this lesson...
                $(".next-video-button").prop("disabled", true); //Turn off the next button.
            }
        }

        //Loads the previous video via the previous button.
        function previousVideoPressed() {
            console.log("Loading previous video...");
            $(".next-video-button").prop("disabled", false); //We are going back, so there must be a next video, so enable the next button.
            if (lessonVideoIndex > 0) { //If there is another video prior to this one in this lesson...
                lessonVideoIndex--; //Advance to it.
                loadLessonVideo(); //Load it.
                if (lessonVideoIndex == 0) { //If this is the first video of the lesson...
                    $(".previous-video-button").prop("disabled", true); //Disable the previous button.
                }
            } else { //If there is not another video prior to this one in this lesson...
                $(".previous-video-button").prop("disabled", true); //TUrn off the previous button.
            }
        }

        //Gets the video object from the videoList by comparing vids to the passed vid.
        function getVideoObjectOfVideoWithVid(vid) {
            let video = null;
            videoList.forEach((currentVideo) => {
                if (currentVideo.vid == vid) {
                    video = currentVideo
                }
            });

            return video;
        }

        //Changes the page to display the lesson data.
        function goToLessons() {
            $('.lessons').show(); //Show the lesson data.
            $('.videos').hide(); //Hide the video data.

            $('#videos-button').addClass('btn-default'); //Switch the active button colors.
            $('#lessons-button').addClass('btn-primary');
            $('#videos-button').removeClass('btn-primary');
            $('#lessons-button').removeClass('btn-default');
        }

        //Changes the page to display the video data.
        function goToVideos() {
            $('.videos').show();
            $('.lessons').hide();

            $('#videos-button').addClass('btn-primary');
            $('#lessons-button').addClass('btn-default');
            $('#videos-button').removeClass('btn-default');
            $('#lessons-button').removeClass('btn-primary');
        }

        //Sends the user to the top of the html page via animation from jquery.
        function toTop() {
            $("html, body").animate({scrollTop: 0}, 300);
        }

        //Called when submitting a new lesson. This function compiles all the data into three arrays so that we can send it off to the php in a reasonable way. The form inputs are dynamic (based on how many videos are in the database), so we can't guess what data we would be sending otherwise.
        function compileLessonFormData() {
            let form = document.forms["lesson-form"]; //Get the form.
            let vids = []; //Create the arrays.
            let startSeconds = [];
            let endSeconds = [];

            <?php for ($i = 0; $i < count($videoVidArray); $i++) { ?> //For every video in the databse...
            if (form["<?php print $videoTitleArray[$i]; ?>-include"].checked == true) { //If this video should be added to the lesson...
                vids.push(<?php print $videoVidArray[$i]; ?>); //Push the vid of this video onto the vid array.
                startSeconds.push(form["<?php print $videoTitleArray[$i]; ?>-start"].value); //Push the value of the startSeconds field for this video on the startSeconds array.
                endSeconds.push(form["<?php print $videoTitleArray[$i]; ?>-end"].value); //Push the value of the endSeconds field for this video on the endSeconds array.
            }
            <?php
            } ?>

            form["newLessonVids"].value = vids; //Set the hidden input values to the three arrays.
            form["newLessonStartSeconds"].value = startSeconds;
            form["newLessonEndSeconds"].value = endSeconds;

            return true; //Allow the form to submit.
        }
    </script>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <!--100% / 12 * 10-->
        <div class="col-md-10 col-md-offset-1 content">
            <div class="container-fluid">
                <div class="row">
                    <!--^^^ / 12 * 12-->
                    <div class="col-md-12">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-md-12">
                                    <h2>Project 2-3: Video-Based Learning Tool</h2>
                                </div>
                            </div>
                            <div class="row player">
                                <div class="col-md-2 nav-wrapper">
                                    <button class="btn btn-primary video-nav-button previous-video-button" onclick="previousVideoPressed(); toTop();" disabled>Previous Video</button>
                                </div>
                                <!--^^^ / 12 * 8-->
                                <div class="col-md-8 nav-wrapper">
                                    <!--The YouTube player goes below this line.-->
                                    <div id="embedded-youtube-player">Your browser does not support YouTube's embedded video player (or something went wrong, check the console. In Google Chrome press F12 then click Console).</div>
                                </div>
                                <div class="col-md-2 nav-wrapper">
                                    <button class="btn btn-primary video-nav-button next-video-button" onclick="nextVideoPressed(); toTop();" disabled>Next Video</button>
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
                                        <th>Title (Click to load)</th>
                                        <th>Concepts (Click to load)</th>
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
                                                            <br/>
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
                                        <th>Segments (Click to load)</th>
                                    </tr>
                                    <?php for ($i = 0; $i < count($lessonLidArray); $i++) { ?>
                                        <tr>
                                            <td>
                                                <button class="btn btn-warning" onclick="loadLesson(<?php print ($lessonLidArray[$i] - 1); ?>); toTop();"><?php print $lessonTitleArray[$i]; ?></button>
                                            </td>
                                            <td>
                                                <?php for ($j = 0; $j < count($lessonVideoLvidArray); $j++) {
                                                    if ($lessonVideoLidArray[$j] == $lessonLidArray[$i]) { ?>
                                                        <button class="btn btn-info btn-xs" onclick="loadSegment(<?php print ($lessonVideoLvidArray[$j] - 1); ?>); toTop();"><?php print videoTitleWithVid($lessonVideoVidArray[$j], $videoVidArray, $videoTitleArray) . " (" . ($lessonVideoEndSecondsArray[$j] - $lessonVideoStartSecondsArray[$j]) . "s)"; ?></button>
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
