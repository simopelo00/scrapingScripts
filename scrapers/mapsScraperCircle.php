<?php
// php mapsScraperCity.php palestra milano (se spazi usare "")
// inserire latitudine e longitudine della città da analizzare
$lat = "45.4642";
$lon = "9.1900";
$radius = 0.1;
$step_length = 0.008;
$step = 0;
$length = 0;
$jjj=0;
$query = $argv[1];
$city = $argv[2];
$output = fopen(str_replace(" ", "_", $query)."_".str_replace(" ", "_", $city).".csv", "w");
$places_id = [];
while ($length/2 <= $radius) {
	$height = $radius-$step*$step_length;
	$length = sqrt(pow($radius, 2)-pow($height, 2));
	$lonMin = $lon-$length;
	$lonMax = $lon+$length;
	while($lonMin<=$lonMax){
		sleep(1.5);
		$lonMin+=$step_length;
		$latitude = $lat-$height;
		$response = placeRequest($latitude,$lonMin,$query);
		echo $jjj." ".$latitude.",".$lonMin." status:".$response->status."\n";
		foreach ($response->results as $result) {
			$resultLat = $result->geometry->location->lat;
			$resultLon = $result->geometry->location->lng;
			//Modificare il paese in base alla lingua impostata
			if(strpos($result->formatted_address, "Italia")>0){
				//Modificare per escludere types non voluti
				if(!in_array("car_rental", $result->types)){
					if(!in_array($result->place_id, $places_id)){
						$place = idRequest($result->place_id);
						array_push($places_id, $result->place_id);
						print_r($place);
						if($place["website"] != ""){
							fputcsv($output, $place);
						}
					} else echo "Già preso\n";
				}
			}
		}
		$jjj++;
	}
	$step++;
}
fclose($output);

function idRequest($placeId){
	sleep(1.2);
	$place = [];
	$response = json_decode(file_get_contents("https://maps.googleapis.com/maps/api/place/details/json?placeid=".$placeId."&key=AIzaSyCV_RUCS0N93h0M8HUu4pYE8PSBLxGkiZ8"));
	$place["name"] = $response->result->name;
	$place["website"] = $response->result->website??"";
	$place["city"] = $response->result->formatted_address;
	return $place;
}

function placeRequest($lat,$lon,$query){
	$mapsQueryStart = "https://maps.googleapis.com/maps/api/place/textsearch/json?";
	$location = "location=".$lat.",".$lon."&radius=500";
	// IMPOSTARE LA LINGUA DI RICERCA
	$query = "&language=it&query=".str_replace(" ", "+", $query);
	$mapsQueryEnd = "&key=AIzaSyCV_RUCS0N93h0M8HUu4pYE8PSBLxGkiZ8";
	return json_decode(file_get_contents($mapsQueryStart.$location.$query.$mapsQueryEnd));
}