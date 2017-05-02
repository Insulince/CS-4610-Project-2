<?php
$dbhost = "localhost";
$dbuser = "root";
$dbpassword = "";
$dbname = "myvideodb";

$connection = mysql_connect($dbhost, $dbuser, $dbpassword);

if (!$connection) {
    die('Could not connect: ' . mysql_error());
}

mysql_select_db($dbname, $connection);

$vidArr = array();
$ytIdArr = array();
$titArr = array();
$descArr = array();

$query = "SELECT * FROM video";
$result = mysql_query($query);

if ($result) {
    while ($row = mysql_fetch_assoc($result)) {
        $vidArr[] = $row['vid'];
        $ytIdArr[] = $row['youtube_id'];
        $titArr[] = $row['title'];
        $descArr[] = $row['description'];
    }
}

$myplaylist = "[";

for ($u = 0; $u < count($ytIdArr); $u++) {
    $myplaylist = $myplaylist . "'" . $ytIdArr[$u] . "'";

    if ($u < count($ytIdArr) - 1) {
        $myplaylist = $myplaylist . ", ";
    }
}

$myplaylist = $myplaylist . "]";

mysql_close($connection);
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Video Player</title>
        <style type="text/css">
            .wd1 {
                width: 8em;
            }
            .ht1 {
                height: 4em;
            }
        </style>
    </head>
    <body>
      <!-- 1. The <iframe> (and video player) will replace this <div> tag. -->
        <b>Playlist</b> &nbsp; &nbsp;<br />
        <div id="player"></div>
        <hr />
        <span id='myplaylist'><?php print $myplaylist; ?></span>
        <script>
            // 2. This code loads the IFrame Player API code asynchronously.
            var tag = document.createElement('script');

            tag.src = "https://www.youtube.com/iframe_api";
            var firstScriptTag = document.getElementsByTagName('script')[0];
            firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

            // 3. This function creates an <iframe> (and YouTube player)
            //    after the API code downloads.
            // var playlist = ['dQ2YNKbGqFc', 'rIBRcQdzWQs', 'UVUwqxuDb9A', 'SZUcEmREZ9Y', 'tFdlhlmQ-ek'];
            var playlist = <?php print $myplaylist; ?>;
            var player;
            function onYouTubeIframeAPIReady() {
                if (playlist.length > 0) {
                    player = new YT.Player('player', {
                        height: '390', width: '640',
                        videoId: playlist[0],
                        events: {
                            'onReady': onPlayerReady,
                            'onStateChange': onPlayerStateChange,
                            'onError': onPlayerError
                        }
                    });
                    playlist = playlist.slice(1, (playlist.length));
                }
            }

            // 4. The API will call this function when the video player is ready.
            function onPlayerReady(event) {
                event.target.playVideo();
            }

            function onPlayerError(event) {
                if (playlist.length == 0)
                    return;
                var nextVideo = playlist[0];
                if (nextVideo != '') {
                    player.loadVideoById(nextVideo);
                    playlist = playlist.slice(1, (playlist.length));
                }
            }

            // 5. The API calls this function when the player's state changes.
            //    The function indicates that when playing a video (state=1),
            //    the player should play for six seconds and then stop.
            function onPlayerStateChange(event) {
                if (event.data == YT.PlayerState.ENDED)
                {
                    if (playlist.length == 0)
                        return;
                    var nextVideo = playlist[0];
                    if (nextVideo != '') {
                        player.loadVideoById(nextVideo);
                        playlist = playlist.slice(1, (playlist.length));
                    }
                }
            }
            function stopVideo() {
                player.stopVideo();
            }
            function pauseVideo() {
                player.pauseVideo();
            }
        </script>
    </body>
</html>