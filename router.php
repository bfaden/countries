<?php
const RESTCOUNTRIES_URL = "https://restcountries.com/v3.1/";

if (!preg_match('#^/endpoint/#', $_SERVER["REQUEST_URI"])) {
	return false;    // serve the requested resource as-is.
}

// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
	if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
		header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

	if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
		header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

	exit(0);
}

$countryName = $_POST['name'] ? $_POST['name'] : '';

$results = [];

switch ($_POST['searchBy']) {
	case 'code':
		$results = json_decode(@file_get_contents(RESTCOUNTRIES_URL . "alpha/" . rawurlencode($countryName)));
		break;
	case 'name':
		if($countryName == '') {
			$results = json_decode(@file_get_contents(RESTCOUNTRIES_URL . "all"));
		} else {
			$results = json_decode(@file_get_contents(RESTCOUNTRIES_URL . "name/" . rawurlencode($countryName)));
		}
		
		break;
}

if (empty($results)) {
	echo json_encode(["responseCode" => -1, "responseMessage" => "No Results were found"]);
	return;
}

// sort by population
usort($results, function ($a, $b) {
	return $a->population == $b->population ? 0 : ($a->population > $b->population ? -1 : 1);
});

$response = ['responseCode' => 0, "results" => array_slice($results, 0, 50)];

echo json_encode($response);
