<?php

class YoutubeManager
{

    private static $resStatus = 'Success';
    private static $resMsg;
    private static $resData = array();

    private static $curlHeader = array(
        "user-agent: Mozilla/5.0 (Linux; U; Android 4.4.2; en-us; SCH-I535 Build/KOT49H) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30"
    );

    public static function getVideoData($videoId)
    {

        if (empty($videoId)) {
            self::$resStatus = "Error";
            self::$resMsg = 'Video Id is required';
            return self::showResponse();
        }

        $curlUrl = "https://www.youtube.com/watch?v=" . $videoId;
        $curl = curl_init($curlUrl);
        curl_setopt($curl, CURLOPT_URL, $curlUrl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, self::$curlHeader);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        $res = curl_exec($curl);
        if (curl_errno($curl)) {
            self::$resStatus = "Error";
            return self::showResponse();
        }
        curl_close($curl);
        if (empty($res)) {
            self::$resStatus = "Error";
            self::$resMsg = 'No data found';
            return self::showResponse();
        }

        //exploring response
        $ex1 = explode("var ytInitialPlayerResponse = {", $res);


        if (!isset($ex1[1])) {
            self::$resStatus = "Error";
            self::$resMsg = 'No data found';
            return self::showResponse();
        }
        $data = explode(';</script>', $ex1[1]);
        if (!isset($data[0])) {
            self::$resStatus = "Error";
            self::$resMsg = 'No data found';
            return self::showResponse();
        }
        $json = json_decode("{" . $data[0], true);
        if (!$json) {
            self::$resStatus = "Error";
            self::$resMsg = 'No data found';
            return self::showResponse();
        }
        $videos = [];
        $i = 0;

        if (count($json["streamingData"]) > 0) {
            foreach ($json["streamingData"]["adaptiveFormats"] as $video) {
                $videos[$i]["itag"] = $video["itag"];
                $videos[$i]["link"] = isset($video["url"]) ? $video["url"] : explode("url=", $video["signatureCipher"])[1];
                $videos[$i]["mimeType"] = explode(";", $video["mimeType"])[0];
                $videos[$i]["quality"] = isset($video["qualityLabel"]) ? $video["qualityLabel"] : $video["quality"];
                $i++;
            }
            self::$resData = [
                "title" => $json["videoDetails"]["title"],
                "viewCount" => $json["videoDetails"]["viewCount"],
                "channelId" => $json["videoDetails"]["channelId"],
                "author" => $json["videoDetails"]["author"],
                "thumbnail" => $json["videoDetails"]["thumbnail"]["thumbnails"][2]["url"],
                "links" => $videos
            ];
        } else {
            self::$resStatus = "Error";
            self::$resMsg = 'No data found';
        }
        return self::showResponse();

    }

    public static function showResponse() {
        return [
            'status' => self::$resStatus,
            'message' => self::$resMsg,
            'data' => self::$resData,
        ];

    }
    public static function downloadVideo($videoUrl, $title, $extension = 'mp4')
    {
        $fileName = $title . '.' . $extension;


        if (!empty($videoUrl)) {
            header("Cache-Control: public");
            header("Content-Disposition: attachment;filename=\"$fileName\"");
            header('Content-Type: video/mp4');
            readfile($videoUrl);
        }
    }
}
