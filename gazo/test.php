<?php
	// APIキー
	$api_key = "AIzaSyBFw7wliDGmN9BeRTJW0NoTsPVTulfqPzU" ;

	// リファラー (許可するリファラーを設定した場合)
	$referer = "https://net-navi.cc/" ;

	// 画像へのパス
	//$image_path = "./img/korea1.jpg" ;
	$image_path = "/tmp/4236760834614" ;

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
	echo $json ;

	echo "<h2>ヘッダー</h2>" ;
	echo $header ;