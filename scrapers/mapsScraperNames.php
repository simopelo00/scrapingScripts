<?php

// mapsScraperNames.php file.csv
/* il file csv deve essere del tipo
	nome,città
	nome due,città
	...
*/

require("simple_html_dom.php");

$output = fopen("output_".$argv[1], "w");
$places = array_map('str_getcsv', file($argv[1]));
foreach ($places as $place) {
	$placeInfo = json_decode(file_get_contents(mapsQuery($place[0], $place[1])));
	$place_id = $placeInfo->results[0]->place_id;
	$place = idRequest($place_id);
	fputcsv($output, $places);
	var_dump($place);
}
fclose($output);

function mapsQuery($placeName, $placeCity){
	$mapsQueryStart = "https://maps.googleapis.com/maps/api/place/textsearch/json?query=";
	$mapsQueryEnd = "+".str_replace(" ", "+", $placeCity)."&key=AIzaSyCV_RUCS0N93h0M8HUu4pYE8PSBLxGkiZ8";
	return $mapsQueryStart.str_replace(" ","+",$placeName).$mapsQueryEnd;
}

function idRequest($placeId){
	sleep(0.8);
	$place = [];
	$response = json_decode(file_get_contents("https://maps.googleapis.com/maps/api/place/details/json?placeid=".$placeId."&key=AIzaSyCV_RUCS0N93h0M8HUu4pYE8PSBLxGkiZ8"));
	$place["name"] = $response->result->name;
	$place["website"] = $response->result->website??"";
	$place["city"] = $response->result->formatted_address;
	return $place;
}