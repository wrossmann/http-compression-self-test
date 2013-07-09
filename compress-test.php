<?php
/******************************
* HTTP Compression Self-Test
* Source:   http://sammitch.ca
* Version:  1.0
* Date:     Jan 24, 2013
******************************/

// make sure cURL is available
if( ! function_exists('curl_init') ) {
  die('You must have the cURL extension installed/enabled.');
// make sure the script isn't being called via command line
} else if( php_sapi_name() == 'cli' ) {
	die('This script must be invoked via a web browser.');
}
// use a $_GET var to prevent infinite looping through HTTP requests
if( ! isset($_GET['self']) ) {
	// common compression encodings we want to check for in order of desirability
	// 'identity' means uncompressed
	$encodings = array(
		'gzip' => '1.0',
		'deflate' => '0.5',
		'compress' => '0.5',
		'identity' => '0.1'
	);

	// create the header string
	$temp = array();
	foreach( $encodings as $key => $value ) {
		$temp[] = sprintf('%s;q=%s', $key, $value);
	}
	$accept_header = 'Accept-Encoding: ' . implode(', ', $temp);

	echo "<pre>Requesting the following encodings:\n\t" . $accept_header . "</pre>";

	// prepare cURL
	$uri = $_SERVER['SCRIPT_URI'] . '?self';
	$ch = curl_init($uri);
	curl_setopt_array($ch, array(
		CURLOPT_HEADER => TRUE,
		CURLOPT_NOBODY => TRUE,
		CURLOPT_RETURNTRANSFER => TRUE,
		CURLOPT_HTTPHEADER => array ($accept_header)
	));
	if( ! $return = curl_exec($ch) ) {
		die('curl_exec failed: ' . curl_error($ch));
	}

	// check returned headers
	$recv_enc = NULL;
	foreach( explode("\r\n",$return) as $ret_header ) {
		$h_arr = explode(':', $ret_header);
		if( $h_arr[0] == 'Content-Encoding' ) {
			$recv_enc = trim($h_arr[1]);
			break;
		}
	}

	// print results.
	echo '<pre>';
	if( ! (is_null($recv_enc) || $recv_enc == 'identity') ) {
		echo 'Received Content-Encoding: ' . $recv_enc;
	} else {
		echo 'Content was received uncompressed.';
	}
	echo '</pre>';

} else {
	// if $_GET['self'] is present print out an itty-bitty test page
	echo <<<_EOF_
<!DOCTYPE HTML>
<html>
<head>
	<title>Test page</title>
</head>
<body>
<h1>Test Page</h1>
</body>
</html>
_EOF_;
}
