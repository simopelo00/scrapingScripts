<?php
// php getEmail.php nomefile.csv
/*
	File:
		website
		website
		website
		...
*/
include 'vendor/autoload.php';
use Sunra\PhpSimple\HtmlDomParser;
use hbattat\VerifyEmail;
use JonnyW\PhantomJs\Client;

$output = fopen("output_".$argv[1], "w");
$websites = str_getcsv(file_get_contents($argv[1]), "\n");
foreach ($websites as $website) {
	$email = findEmail($website);
	if($email == ""){
		foreach (getLinks($website) as $link) {
			$email = findEmail($link);
			if($email != ""){
				break;
			}
		}
	}
	if($email == ""){
		$email = findEmail(getPhantomWebsite($website));
	}
	if($email == "" || !testEmail($email)){
		if(testEmail("info@".getDomain($website)) && getDomain($website) != "facebook.com" && getDomain($website) != "tripadvisor.it"){
			$email = "info@".getDomain($website);
		}
	}
	var_dump($website.",".$email);
	sleep(1.2);
	fputcsv($output, [$website, $email]);
}
fclose($output);

function findEmail($url){
	ini_set('user_agent','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.79 Safari/537.36');
	$website = @file_get_contents($url);
	$email = "";
	preg_match_all("/[\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+/i", $website, $matches);
	foreach (array_unique($matches[0]) as $match) {
		$ext = substr($match, strrpos($match, '.') + 1);
		if(filter_var($match, FILTER_VALIDATE_EMAIL) && $match != "email@mail.com" && $match != "info@icvda.it" && $ext != "png" && $ext != "jpg" && $ext != "gif"){
			$email = $match;
		}
	}
	return $email;
}

function testEmail($email){
	$ve = new VerifyEmail($email, 'pfagiolone@gmail.com');
	$verify = $ve->verify();
	if(count($ve->get_debug(), COUNT_RECURSIVE) < 19){
		$verify = true;
	} elseif(strpos($ve->get_debug()[15], "Spamhaus") > 0 || strpos($ve->get_debug()[15], "Recipient address rejected") > 0){
		$verify = true;
	}
	return $verify;
}

function getDomain($url){
  $pieces = parse_url($url);
  $domain = isset($pieces['host']) ? $pieces['host'] : $pieces['path'];
  if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
    return $regs['domain'];
  }
  return false;
}

function getLinks($url){
	$iii = 0;
	$links = [];
	$website = @file_get_contents($url);
	$domain = getDomain($url);
	if($website){
		$domParser = HtmlDomParser::str_get_html($website);
		if(!empty($domParser)){
			foreach ($domParser->find("a") as $a) {
				$link = $a->href;
				if($url != $link && substr($link, 0, 6) != "mailto" && substr($link, 0, 4) != "tel:"){
					if(strpos($link, $domain)){
						array_push($links, $link);
					} else {
						if(substr($link, 0, 1) === '/'){
							array_push($links, $domain.$link);
						} elseif(strpos(@getDomain($link), "facebook") || strpos(@getDomain($link), "youtube")) {
							array_push($links, $domain."/".$link);
						}
					}
					$iii++;
				}
				if($iii > 10) break;
			}
		}
	}
	return $links;
}

function getPhantomWebsite($url){
	$client = Client::getInstance();
	$client->getEngine()->setPath('/usr/lib/phantomjs/phantomjs');
	$request = $client->getMessageFactory()->createRequest($url, 'GET');
	$response = $client->getMessageFactory()->createResponse();
	$client->send($request, $response);
	return $response->getContent();
}