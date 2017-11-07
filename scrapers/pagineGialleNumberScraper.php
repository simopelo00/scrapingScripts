<?php

require("simple_html_dom.php");
if (ob_get_level() == 0) ob_start();

// EDIT NOME FILE IN OUTPUT
$output = fopen("output.csv", "w");

// MODIFICA QUESTI DUE ARRAY CON LE RICERCHE DA FARE
$activities = ["amministrazione condominiale","istituto scolastico privato"];
$cities = ["milano", "torino"];

foreach ($cities as $city) {
	$result = [];
	$result["city"] = $city;
	foreach ($activities as $activity) {
		sleep(2);
		$result["activity"] = $activity;

		$url = "https://www.paginegialle.it/ricerca/".str_replace(" ", "%20", $activity)."/".str_replace(" ", "%20", $city);
		$search = file_get_contents($url);
		$domParser = new \simple_html_dom($search);
		$result["number"] = str_replace(" ", "", $domParser->find('.searchResNum', 0)->plaintext);

		var_dump($result);
		fputcsv($output, $result);
		ob_flush();
		flush();
	}
}

fclose($output);
ob_end_flush();