<?php

require_once('YoutubeManager.class.php');

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS, post, get');
header("Access-Control-Max-Age", "3600");
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');
header("Access-Control-Allow-Credentials", "true");

if (isset($_GET['getVideoData']) && isset($_GET['videoId']) && $_GET['videoId'] != "") {
    $ytData = YoutubeManager::getVideoData($_GET['videoId']);
    echo json_encode($ytData);
    // print_r($ytData);
}


if (isset($_GET['downloadVideo']) && 
    isset($_GET['videoUrl']) && $_GET['videoUrl'] != "" &&
    isset($_GET['title']) && $_GET['title'] != "" &&
    isset($_GET['extension']) && $_GET['extension'] != "" 
) {
    YoutubeManager::downloadVideo(urldecode($_GET['videoUrl']),urldecode($_GET['title']),$_GET['extension']);
}



