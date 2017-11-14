<?php
// php pgScraper.php gioielleria lombardia
require_once("simple_html_dom.php");

$url = "https://www.paginegialle.it/ricerca/".str_replace(" ", "%20", $argv[1])."/".str_replace(" ", "%20", $argv[2]);
$output = fopen(str_replace(" ", "%20", $argv[1])."_".str_replace(" ", "%20", $argv[2]).".csv", "w");
ini_set('user_agent','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.79 Safari/537.36');
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
		$result["address"] = "";
		$resultWebsite = "";
		$result["name"] = trim($element->find(".elementTitle",0)->plaintext);
		$activityPage = $element->find(".elementTitle a",0)->href;
		if($element->find(".elementAddress",0)){
			$address = $element->find(".elementAddress",0)->plaintext;
			$trimAddress = trim(preg_replace('/\t/i', '', $address));
			$stripAddress = preg_replace('!\s+!', ' ', $trimAddress);
			$encodedAddress = substr($stripAddress, 0, strpos($stripAddress,')')+1);
			$result["address"] = html_entity_decode($encodedAddress, ENT_QUOTES, 'UTF-8');
		}
		if($element->find(".phoneNumbers",0)){
			$result["phone"] = $element->find(".phoneNumbers",0)->plaintext;
		}
		sleep(1.4);
		if(strpos($activityPage, "mypoints.paginegialle.it")>0){
			$resultWebsite = $activityPage;
		} else {
			$singlePage = @file_get_contents($activityPage);
			if($singlePage){
				$activityParser = new \simple_html_dom($singlePage);
				if($activityParser){
					$resultWebsite = $activityParser->find(".nav_scheda .shinystat_ssxl",0)->href;
				}
			}
		}
		sleep(1.5);
		$result["website"] = $resultWebsite;
		if($result["website"] != ""){
			fputcsv($output, $result);
		}
		print_r($result);
	}
}
fclose($output);
