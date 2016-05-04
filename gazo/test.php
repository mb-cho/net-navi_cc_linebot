<?php
	// API�L�[
	$api_key = "AIzaSyBFw7wliDGmN9BeRTJW0NoTsPVTulfqPzU" ;

	// ���t�@���[ (�����郊�t�@���[��ݒ肵���ꍇ)
	$referer = "https://net-navi.cc/" ;

	// �摜�ւ̃p�X
	//$image_path = "./img/korea1.jpg" ;
	$image_path = "/tmp/4236760834614" ;

	// ���N�G�X�g�p��JSON���쐬
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

	// ���N�G�X�g�����s
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

	// �擾�����f�[�^
	$json = substr( $res1, $res2["header_size"] ) ;				// �擾����JSON
	$header = substr( $res1, 0, $res2["header_size"] ) ;		// ���X�|���X�w�b�_�[

	// �o��
	echo "<h2>JSON</h2>" ;
	echo $json ;

	echo "<h2>�w�b�_�[</h2>" ;
	echo $header ;