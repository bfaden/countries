<?php
require_once __DIR__ . '/vendor/autoload.php';

header('Access-Control-Allow-Origin: *');

const RESTCOUNTRIES_URL = "https://restcountries.eu/rest/v2/";

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

$klein = new \Klein\Klein();

$klein->respond('/endpoint/?[:name]?', function ($request, $response) {
	$countryName = $request->name ? $request->name : '';
	
	$results = [];
	
	if(!count($countryName)) {
		$results = json_decode(@file_get_contents(RESTCOUNTRIES_URL . "all"));
	} elseif(strlen($countryName) == 2 || strlen($countryName) == 3) {
		$results = json_decode(@file_get_contents(RESTCOUNTRIES_URL . "alpha/" . rawurlencode($countryName)));
		$results = $results ? [$results] : [];
	}
	
	if (empty($results)) {
		$results = json_decode(@file_get_contents(RESTCOUNTRIES_URL . "name/" . rawurlencode($countryName)));
	}
	
	if (empty($results)) {
		echo json_encode(["responseCode" => -1, "responseMessage" => "No Results were found"]);
		return;
	}
	
	// sort alphabetically
	usort($results, function ($a, $b) use ($request) {
		if(isset($request->paramsPost()->orderBy) && $request->paramsPost()->orderBy === "population" ) {
			return $a->population == $b->population ? 0 : ($a->population > $b->population ? -1 : 1);
		} else {
			return strcmp($a->name, $b->name);
		}
	});
	
	$response = ['responseCode' => 0, "results" => array_slice($results, 0, 50)];
	
	echo json_encode($response);
});

$klein->dispatch();
