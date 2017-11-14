<?php
// php tripadvisorScraper.php rimini 187807
// l'id bisogna cercarlo online andando su tripadvisor e copiando il codice accanto a g
require("simple_html_dom.php");
if (ob_get_level() == 0) ob_start();
$cityName = $argv[1];
$cityId = $argv[2];
$output = fopen("./qdp/".str_replace(" ", "_", $cityName).".csv", "w");
// EDIT CATEGORIE: dopo cat= il primo id è scritto normale mentre quelli successivi sono distanziati con %2C (guarda file categorie_tripadvisor.csv)
$urlStart = "https://www.tripadvisor.it/RestaurantSearch?Action=PAGE&geo=".$cityId."&ajax=1&cat=10641%2C4617%2C10643%2C10640%2C10651%2C10621%2C10642%2C10686%2C10654%2C10646%2C10683%2C10668%2C10649%2C10670%2C10345&itags=10591&sortOrder=relevance&o=a";
$urlEnd = "&availSearchEnabled=true&eaterydate=2017_10_13&date=2017-10-14&time=20%3A00%3A00&people=2";

$domParser = new \simple_html_dom(file_get_contents($urlStart."00".$urlEnd));
$records = (trim($domParser->find(".pageNum",-1)->plaintext)-1)*30;
var_dump($records);
for($iii=0;$iii<=$records;$iii+=30){
	$result = file_get_contents($urlStart.$iii.$urlEnd);
	$domParser = new \simple_html_dom($result);
	foreach ($domParser->find('.listing') as $element) {
		sleep(1.2);
		$restaurantDom = new \simple_html_dom($element);
		$restaurant = [];
		foreach ($restaurantDom->find('.property_title') as $name) {
			$restaurant["name"] = $name->plaintext;
			$restaurantLink = $name->href;
		}

		$cuisines = [];
		foreach ($restaurantDom->find('.cuisine') as $cuisine) {
			array_push($cuisines, $cuisine->plaintext);
		}
		$restaurant["cuisines"] = json_encode($cuisines);

		$restaurant["website"] = "no";
		$restaurant["place"] = $cityName;

		$mapsQueryStart = "https://maps.googleapis.com/maps/api/place/textsearch/json?query=";
		$mapsQueryEnd = "+".str_replace(" ", "+", $cityName)."&key=AIzaSyCV_RUCS0N93h0M8HUu4pYE8PSBLxGkiZ8";
		$mapsQuery = $mapsQueryStart.str_replace(" ","+",$restaurant["name"]).$mapsQueryEnd;
		$restaurantInfo = json_decode(file_get_contents($mapsQuery));
		$place_id = $restaurantInfo->results[0]->place_id;
		sleep(1.2);
		$mapsByIdStart = "https://maps.googleapis.com/maps/api/place/details/json?placeid=";
		$mapsByIdEnd = "&key=AIzaSyCV_RUCS0N93h0M8HUu4pYE8PSBLxGkiZ8";
		$mapsById = $mapsByIdStart.$place_id.$mapsByIdEnd;
		$place = json_decode(file_get_contents($mapsById));
		$restaurant["place_id"] = $place_id;
		if($place->result->website != ""){
			$restaurant["website"] = $place->result->website;
		} else $restaurant["website"] = "https://www.tripadvisor.it/".$restaurantLink;

		print_r($restaurant);

		fputcsv($output, $restaurant, ";");

		ob_flush();
		flush();
	}
}
ob_end_flush();
fclose($output);