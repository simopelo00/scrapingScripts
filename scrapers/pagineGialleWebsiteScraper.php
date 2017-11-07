<?php
// php pgScraper.php gioielleria lombardia
require_once("simple_html_dom.php");

$url = "https://www.paginegialle.it/ricerca/".str_replace(" ", "%20", $argv[1])."/".str_replace(" ", "%20", $argv[2]);
$output = fopen(str_replace(" ", "%20", $argv[1])."_".str_replace(" ", "%20", $argv[2]).".csv", "w");
$page = file_get_contents($url);
$domParser = new \simple_html_dom($page);
$pagesNumber = ceil(trim($domParser->find('.searchResNum', 0)->plaintext)/20);
var_dump($pagesNumber);
sleep(1.2);
for($iii=1;$iii<$pagesNumber;$iii++){
	echo "Pagina: ".$iii."/".$pagesNumber."\n";
	$pageUrl = $url."/p-".$iii;
	$pageParser = new \simple_html_dom(file_get_contents($pageUrl));
	foreach ($pageParser->find(".listElementsInnerWrapper .listElement") as $element) {
		$result = [];
		$result["name"] = "";
		$result["website"] = "";
		$resultWebsite = "";
		$result["name"] = trim($element->find(".elementTitle",0)->plaintext);
		$activityPage = $element->find(".elementTitle a",0)->href;
		sleep(1.2);
		if(strpos($activityPage, "mypoints.paginegialle.it")>0){
			$resultWebsite = $activityPage;
		} else {
			$singlePage = @file_get_contents($activityPage);
			$activityParser = new \simple_html_dom($singlePage);
			if($activityParser->find(".nav_scheda .shinystat_ssxl",0)){
				$resultWebsite = $activityParser->find(".nav_scheda .shinystat_ssxl",0)->href;
			}
			sleep(1.1);
		}
		$result["website"] = $resultWebsite;
		if($result["website"] != ""){
			fputcsv($output, $result);
		}
		print_r($result);
	}
}
fclose($output);