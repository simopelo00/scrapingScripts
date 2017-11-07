<?php
// php mapsScraperLocations.php trentino.csv "noleggio sci"
/* il file csv deve essere del tipo
	nome,cap
	nome due,cap
	...
*/
$locations = array_map('str_getcsv', file($argv[1]));
$query = str_replace(" ", "+", $argv[2]);
$output = fopen(pathinfo($argv[1], PATHINFO_FILENAME)."_".str_replace(" ", "_", $argv[2]).".csv", "w");
$places_id = [];
foreach ($locations as $location) {
	$response = placeRequest($query, $location);
	parseResponse($response, $output);
	sleep(1);
}
fclose($output);

function placeRequest($query, $location){
	$mapsQueryStart = "https://maps.googleapis.com/maps/api/place/textsearch/json?";
	// MODIFICARE LA LINGUA DI RICERCA DELLA QUERY
	$query = "&language=en&query=".$query."+".str_replace(" ", "+", $location[0])."+".$location[1];
	$mapsQueryEnd = "&key=AIzaSyCV_RUCS0N93h0M8HUu4pYE8PSBLxGkiZ8";
	return json_decode(file_get_contents($mapsQueryStart.$query.$mapsQueryEnd));
}

function nextTokenRequest($token){
	$mapsQueryStart = "https://maps.googleapis.com/maps/api/place/textsearch/json?";
	$query = "pagetoken=".$token;
	$mapsQueryEnd = "&key=AIzaSyCV_RUCS0N93h0M8HUu4pYE8PSBLxGkiZ8";
	return json_decode(file_get_contents($mapsQueryStart.$query.$mapsQueryEnd));
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

function parseResponse($response, $output){
	foreach ($response->results as $result) {
		if(!in_array($result->place_id, $GLOBALS["places_id"])){
			//Modificare il paese in base alla lingua impostata
			if(strpos($result->formatted_address, "Spain")>0){
				//Modificare per escludere types non voluti
                if(!in_array("car_rental", $result->types) && !in_array("school", $result->types) && !in_array("post_office", $result->types)){
					$place = idRequest($result->place_id);
					array_push($GLOBALS["places_id"], $result->place_id);
					print_r($place);
					if($place["website"] != ""){
						fputcsv($output, $place);
					}
				}
			}
		}
	}
	if(isset($response->next_page_token)){
		$newResponse = nextTokenRequest($response->next_page_token);
		parseResponse($newResponse, $output);
	} else return false;
}