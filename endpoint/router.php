<?php
require_once __DIR__ . '/vendor/autoload.php';

const RESTCOUNTRIES_URL = "https://restcountries.eu/rest/v2/";

$klein = new \Klein\Klein();

$klein->respond('GET', '/?[:name]?', function ($request, $response) {
	$countryName = $request->name ? $request->name : '';
	
	$results = $countryName !== ''
		? json_decode(@file_get_contents(RESTCOUNTRIES_URL . "name/" . rawurlencode($countryName)))
		: json_decode(@file_get_contents(RESTCOUNTRIES_URL . "all"));
	
	if (empty($results)) {
		echo json_encode(["responseCode" => -1, "responseMessage" => "No Results were found"]);
		return;
	}
	
	// sort alphabetically
	usort($results, function ($a, $b) {
		return strcmp($a->name, $b->name);
	});
	
	$response = ['responseCode' => 0, "results" => array_slice($results, 0, 50)];
	
	echo json_encode($response);
});

$klein->dispatch();
