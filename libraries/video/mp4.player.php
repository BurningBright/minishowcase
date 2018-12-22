<?php

$file = $_GET['file'];
$width = $_GET['width'];
$height = $_GET['height'];

?>
<!DOCTYPE html>
<html lang="en">
<head>

    <title>Video.js | HTML5 Video Player</title>
    <link href="http://vjs.zencdn.net/7.0/video-js.min.css" rel="stylesheet">
    <script src="http://vjs.zencdn.net/7.0/video.min.js"></script>
</head>
<body>

  <video id="example_video_1" class="video-js" controls preload="none" width="<?php echo $width?>" height="<?php echo $height?>" data-setup='{}'>
    <source src="../../<?php echo $file ?>" type="video/mp4">
    <p class="vjs-no-js">To view this video please enable JavaScript, and consider upgrading to a web browser that <a href="http://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a></p>
  </video>

  <script>
    var player = videojs('example_video_1', {
        height: '439px',
        width: '640px',
        techOrder: ['html5'], 
        playbackRates: [1, 1.5, 2, 3, 5]
    });
  </script>
</body>

</html>
