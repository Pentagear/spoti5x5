<?php

	// Spotify API credentials
	$client_id     = '<SPOTIFY-APP-ID>';
	$client_secret = '<SPOTIFY-APP-SECRET>';
	$redirect_uri  = 'https://url.to.this.script/oauth';

	// Authorization URL
	$authorize_url = 'https://accounts.spotify.com/authorize';
	$token_url     = 'https://accounts.spotify.com/api/token';

	// Spotify API endpoint
	$top_artists_url = 'https://api.spotify.com/v1/me/top/artists?limit=25';
	$top_tracks_url = 'https://api.spotify.com/v1/me/top/tracks?limit=25';

	// Parse the request URI...
	$path = array_filter( explode( '/', trim( parse_url( $_SERVER[ 'REQUEST_URI' ], PHP_URL_PATH ), '/' ) ) );

	// STEP 1: Authorization

	if( empty( $path[ 0 ] ) ) {

		header( 'Location: '.$authorize_url.'?'.http_build_query( [
			'client_id'     => $client_id,
			'response_type' => 'code',
			'redirect_uri'  => $redirect_uri,
			'scope'         => 'user-top-read',
		] ) );
		exit;

	}
	if( !in_array( $path[ 0 ], [ 'oauth', 'grid' ] ) ) {
		header( 'Location: /' );
		exit;
	}

	// STEP 2: Access Token

	$access_token = null;
	if( !empty( $path[ 0 ] ) && $path[ 0 ] == 'oauth' ) {

		if( empty( $_GET[ 'code' ] ) ) exit;
		$code = $_GET[ 'code' ];

		$ch = curl_init( $token_url );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt( $ch, CURLOPT_POST, true);
		curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( [
			'grant_type'    => 'authorization_code',
			'code'          => $code,
			'redirect_uri'  => $redirect_uri,
			'client_id'     => $client_id,
			'client_secret' => $client_secret,
		] ) );

		$response = curl_exec( $ch );
		curl_close( $ch );

		$json = json_decode( $response, true );
		setcookie( 'token', $json[ 'access_token' ], strtotime( '+30 days' ), '/' );
		header( 'Location: /grid' );
		exit;

	}

	// STEP 3: Collect Data

	if( !empty( $path[ 0 ] ) && $path[ 0 ] == 'grid' ) {

		if( empty( $_COOKIE[ 'token' ] ) ) {
			header( 'Location: /' );
			exit;
		}

		$ch = curl_init( $top_artists_url );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, [ 'Authorization: Bearer '.$_COOKIE[ 'token' ] ] );

		$response = curl_exec( $ch );
		curl_close( $ch );

		$json = json_decode( $response, true );

		// Get all the 640 images...
		$images = [];
		foreach( $json[ 'items' ] as $item ) {
			foreach( $item[ 'images'] as $image ) {
				if( $image[ 'width' ] == 640 ) {
					$images[] = [
						'url'  => $image[ 'url' ],
						'name' => $item[ 'name' ],
					];
				}
			}
		}

		$final_image = imagecreatetruecolor( 1080, 800 );
		$final_x = 0;
		$final_y = 0;

		$text_color = imagecolorallocate( $final_image, 255, 255, 255 );
		$bg_color_1 = imagecolorallocate( $final_image, 0, 0, 0 );
		$bg_color_2 = imagecolorallocate( $final_image, 32, 32, 32 );

		foreach( $images as $i => $image ) {

			$input_image  = imagecreatefromjpeg( $image[ 'url' ] );
			$input_width  = imagesx( $input_image );
			$input_height = imagesy( $input_image );
			$input_size   = min( $input_width, $input_height );
			$input_x      = ( $input_width - $input_size ) / 2;
			$input_y      = ( $input_height - $input_size ) / 2;

			imagecopyresampled(
				$final_image,
				$input_image,
				$final_x, $final_y,
				$input_x, $input_y,
				160, 160,
				$input_size, $input_size
			);

			imagefilledrectangle(
				$final_image,
				800,
				$i * 32,
				1080,
				( $i * 32 ) + 32,
				( $i % 2 == 0 ? $bg_color_1 : $bg_color_2 )
			);

			imagestring(
				$final_image,     // Destination
				2,                // Font Size
				809,              // X Position
				9 + ( $i * 32 ),  // Y Position
				$image[ 'name' ], // String
				$text_color       // Text Color
			);

			$final_x += 160;
			if( $final_x >= 800 ) {
				$final_x = 0;
				$final_y += 160;
			}

		}

		header( 'Content-type: image/png' );
		imagepng( $final_image );

		imagedestroy( $input_image );
		imagedestroy( $final_image );

	}
