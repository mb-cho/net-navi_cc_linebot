<?php
//gitbucket test
require_once('../line_keys.php');

file_put_contents('access.log', "\n".date('c')."\n ");// Log Clear

//Line
/*
 $channel_id = "1465576377";
 $channel_secret = "aa74822b70eed0736ddc3f8969bcce3e";
 $mid = "u83aeec97dc43b4be650587d4b850f2d4";
*/

// リソースURL設定
$original_content_url_for_image = "[画像URL]";
$preview_image_url_for_image = "[サムネイル画像URL]";
$original_content_url_for_video = "[動画URL]";
$preview_image_url_for_video = "[動画のサムネイル画像URL]";
$original_content_url_for_audio = "[音声URL]";
$download_url_for_rich = "[リッチ画像URL]";

// メッセージ受信
$json_string = file_get_contents('php://input');
$json_object = json_decode($json_string);

log_put(serialize($json_object));

$content = $json_object->result{0}->content;
$text = $content->text;
$from = $content->from;
$message_id = $content->id;
$content_type = $content->contentType;


// ユーザ情報取得
api_get_user_profile_request($from);


// メッセージが画像、動画、音声であれば保存
if (in_array($content_type, array(2, 3, 4))) {
    api_get_message_content_request($message_id);
}
$api_flg = 0;
switch($content_type){
	case 1:
		log_put('recieve 文字');
		break;
	case 2:
		log_put('recieve 画像');
		$api_flg = 2;
		break;
	case 3:
		log_put('recieve 動画');
		$api_flg = 3;
		break;	
	case 4:
		log_put('recieve 音声');
		$api_flg = 4;
		break;	
	default:
		log_put('recieve 不明');
		break;	
}

if($api_fig == 2 ){
	log_put('google_api');
	google_vision_api($message_id);
	return false;
}


// メッセージコンテンツ生成
$image_content = <<< EOM
        "contentType":2,
        "originalContentUrl":"{$original_content_url_for_image}",
        "previewImageUrl":"{$preview_image_url_for_image}"
EOM;
$video_content = <<< EOM
        "contentType":3,
        "originalContentUrl":"{$original_content_url_for_video}",
        "previewImageUrl":"{$preview_image_url_for_video}"
EOM;
$audio_content = <<< EOM
        "contentType":4,
        "originalContentUrl":"{$original_content_url_for_audio}",
        "contentMetadata":{
            "AUDLEN":"240000"
        }
EOM;
$location_content = <<< EOM
        "contentType":7,
        "text":"Convention center",
        "location":{
            "title":"Convention center",
            "latitude":35.61823286112982,
            "longitude":139.72824096679688
        }
EOM;
$sticker_content = <<< EOM
        "contentType":8,
        "contentMetadata":{
          "STKID":"100",
          "STKPKGID":"1",
          "STKVER":"100"
        }
EOM;
$rich_content = <<< EOM
        "contentType": 12,
        "contentMetadata": {
            "DOWNLOAD_URL": "{$download_url_for_rich}",
            "SPEC_REV": "1",
            "ALT_TEXT": "Alt Text.",
            "MARKUP_JSON": "{\"canvas\":{\"width\": 1040, \"height\": 1040, \"initialScene\": \"scene1\"},\"images\":{\"image1\": {\"x\": 0, \"y\": 0, \"w\": 1040, \"h\": 1040}},\"actions\": {\"link1\": {\"type\": \"web\",\"text\": \"Open link1.\",\"params\": {\"linkUri\": \"http://line.me/\"}},\"link2\": {\"type\": \"web\",\"text\": \"Open link2.\",\"params\": {\"linkUri\": \"http://linecorp.com\"}}},\"scenes\":{\"scene1\": {\"draws\": [{\"image\": \"image1\", \"x\": 0, \"y\": 0, \"w\": 1040, \"h\": 1040}],\"listeners\": [{\"type\": \"touch\", \"params\": [0, 0, 1040, 720], \"action\": \"link1\"}, {\"type\": \"touch\", \"params\": [0, 720, 1040, 720], \"action\": \"link2\"}]}}}"
        }
EOM;


// 受信メッセージに応じて返すメッセージを変更
$event_type = "138311608800106203";
if ($text == "image") {
    $content = $image_content;
} else if ($text == "video") {
    $content = $video_content;
} else if ($text == "audio") {
    $content = $audio_content;
} else if ($text == "location") {
    $content = $location_content;
/*
} else if ($text == "sticker") {
    $content = $sticker_content;
*/
} else if ($text == "rich") {
    $content = $rich_content;
} else if ($text == "multi") {
    $event_type = "140177271400161403";
$content = <<< EOM
    "messageNotified": 0,
    "messages": [
        {{$image_content}},
        {{$video_content}},
        {{$audio_content}},
        {{$location_content}},
        {{$sticker_content}},
        {{$rich_content}}
    ]
EOM;
} else { // 上記以外はtext送信
    if ($content_type != 1) {
        $text = "テキスト以外";
    }
$content = <<< EOM
        "contentType":1,
        "text":"ほーほ。「{$text}」でっか？。"
EOM;


}
$post = <<< EOM
{
    "to":["{$from}"],
    "toChannel":1383378250,
    "eventType":"{$event_type}",
    "content":{
        "toType":1,
        {$content}
    }
}
EOM;

api_post_request("/v1/events", $post);

error_log("callback end.");

function api_post_request($path, $post) {
    $url = "https://trialbot-api.line.me{$path}";
    $headers = array(
        "Content-Type: application/json",
        "X-Line-ChannelID: {$GLOBALS['channel_id']}",
        "X-Line-ChannelSecret: {$GLOBALS['channel_secret']}",
        "X-Line-Trusted-User-With-ACL: {$GLOBALS['mid']}"
    );

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($curl);
    log_put($output);
    //error_log($output);
}

function api_get_user_profile_request($mid) {
    $url = "https://trialbot-api.line.me/v1/profiles?mids={$mid}";
    $headers = array(
        "X-Line-ChannelID: {$GLOBALS['channel_id']}",
        "X-Line-ChannelSecret: {$GLOBALS['channel_secret']}",
        "X-Line-Trusted-User-With-ACL: {$GLOBALS['mid']}"
    ); 

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($curl);
    error_log($output);
}

function api_get_message_content_request($message_id) {
    $url = "https://trialbot-api.line.me/v1/bot/message/{$message_id}/content";
    $headers = array(
        "X-Line-ChannelID: {$GLOBALS['channel_id']}",
        "X-Line-ChannelSecret: {$GLOBALS['channel_secret']}",
        "X-Line-Trusted-User-With-ACL: {$GLOBALS['mid']}"
    ); 

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($curl);
    file_put_contents("/tmp/{$message_id}", $output);
}

function log_put($log = ''){

file_put_contents('access.log', "\n".date('c')."\n ".$log, FILE_APPEND);

}

function google_vision_api($message_id){

	require_once('../google_keys.php');

	// 画像へのパス
	//$image_path = "./img/korea1.jpg" ;
	//$image_path = "/tmp/4236868448929" ;
	$image_path = '/tmp/'.$message_id;

	// リクエスト用のJSONを作成
	$json = json_encode( array(
		"requests" => array(
			array(
				"image" => array(
					"content" => base64_encode( file_get_contents( $image_path ) ) ,
				) ,
				"features" => array(
					array(
						"type" => "FACE_DETECTION" ,
						"maxResults" => 3 ,
					) ,
					array(
						"type" => "LANDMARK_DETECTION" ,
						"maxResults" => 3 ,
					) ,
					array(
						"type" => "LOGO_DETECTION" ,
						"maxResults" => 3 ,
					) ,
					array(
						"type" => "LABEL_DETECTION" ,
						"maxResults" => 3 ,
					) ,
					array(
						"type" => "TEXT_DETECTION" ,
						"maxResults" => 3 ,
					) ,
					array(
						"type" => "SAFE_SEARCH_DETECTION" ,
						"maxResults" => 3 ,
					) ,
					array(
						"type" => "IMAGE_PROPERTIES" ,
						"maxResults" => 3 ,
					) ,
				) ,
			) ,
		) ,
	) ) ;

	// リクエストを実行
	$curl = curl_init() ;
	curl_setopt( $curl, CURLOPT_URL, "https://vision.googleapis.com/v1/images:annotate?key=" . $api_key ) ;
	curl_setopt( $curl, CURLOPT_HEADER, true ) ; 
	curl_setopt( $curl, CURLOPT_CUSTOMREQUEST, "POST" ) ;
	curl_setopt( $curl, CURLOPT_HTTPHEADER, array( "Content-Type: application/json" ) ) ;
	curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false ) ;
	curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true ) ;
	if( isset($referer) && !empty($referer) ) curl_setopt( $curl, CURLOPT_REFERER, $referer ) ;
	curl_setopt( $curl, CURLOPT_TIMEOUT, 15 ) ;
	curl_setopt( $curl, CURLOPT_POSTFIELDS, $json ) ;
	$res1 = curl_exec( $curl ) ;
	$res2 = curl_getinfo( $curl ) ;
	curl_close( $curl ) ;

	// 取得したデータ
	$json = substr( $res1, $res2["header_size"] ) ;				// 取得したJSON
	$header = substr( $res1, 0, $res2["header_size"] ) ;		// レスポンスヘッダー

	// 出力
	echo "<h2>JSON</h2>" ;
	//echo $json ;
	$array = json_decode( $json , true ) ;
	
	$data = $array['responses'][0];
	//land_mark
	$land_mark = '';
	
	if(isset($data['landmarkAnnotations'])){
		foreach(($data['landmarkAnnotations'] as $loop){
			$land_mark .= $loop['description'].' or ';
		}
	}
	log_put(trim($land_mark ,' or '));
}
