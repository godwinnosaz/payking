<?php

if (isset($_SERVER['HTTP_ORIGIN'])) {
    // Whitelist specific origins
    $allowedOrigins = [
        'https://paykingweb.com',
        'https://www.paykingweb.com',
        'http://localhost',
        'http://localhost:3000',
    ];

    // Validate and set allowed origin
    if (in_array($_SERVER['HTTP_ORIGIN'], $allowedOrigins)) {
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header("Access-Control-Allow-Credentials: true"); // Needed for cookies or authorization
    }
}

// Allow specific methods
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");

// Allow specific headers
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Expose headers for the client to access
header("Access-Control-Expose-Headers: Content-Length, X-JSON");

// Caching and other headers
header("Access-Control-Max-Age: 86400"); // Cache preflight response for 24 hours
header("Vary: Origin"); // Ensures proper caching behavior for different origins

// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Respond to preflight with a 204 No Content status
    header("HTTP/1.1 204 No Content");
    exit();
}


include_once('jwt_helper.php');

function getData()
{
	$raw = file_get_contents('php://input');
	$data = json_decode($raw, true);
	//print_r($raw);

	if (json_encode($data) === 'null') {
		return $data =  $_POST;
	} else {
		return $data;
	}
}
$val = getData();
if (isset($val['requestID']) && $val['requestID'] === 'api_987654321') {
$dbname = 'u561188727_'.$val['requestID'];
} else {
$response = [
	'status' => false,
	'message' => 'Broken Access'
];
http_response_code(404);
print(json_encode($response));
exit;
}


define('DB_NAME', $dbname );
define('ASSETS', $dbname);
	
?>