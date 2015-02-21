<?php
include('simple_html_dom.php');
error_reporting(0);

function strip_tags_content($text, $tags = '', $invert = FALSE) {
	$text = str_ireplace('<br>', '', $text);
	preg_match_all('/<(.+?)[\s]*\/?[\s]*>/si', trim($tags), $tags);
	$tags = array_unique($tags[1]);
	if(is_array($tags) AND count($tags) > 0) {
		if($invert == FALSE) {
			return preg_replace('@<(?!(?:'. implode('|', $tags) .')\b)(\w+)\b.*?>.*?</\1>@si', '', $text);
		} else {
			return preg_replace('@<('. implode('|', $tags) .')\b.*?>.*?</\1>@si', '', $text);
		}
	} elseif($invert == FALSE) {
		return preg_replace('@<(\w+)\b.*?>.*?</\1>@si', '', $text);
	}
	return $text;
}
 

// inizio configurazione
global $keyword; global $num; global $lingua;
// $keyword può essere una variabile singola o un array in base alle esigenze
// $num è il numero di risultati prelevati per ogni keyword
// $lingua è la lingua in cui Google deve cercare
$keyword = array("italia","spagna","marocco");
$num = 20;
$lingua = 'it';
// fine configurazione


function estrapolazione($key) {
	$q = urlencode(str_replace(' ', '+', $key));

	$r = '';
	global $keyword; global $num; global $lingua;
	if($num > 10) {
		$r = "&num=$num";
	}
	$data = file_get_html('http://www.google.com/search?hl=' .$lingua . '&q='.$q.$r);
	$html = str_get_html($data);
	$ris = array();
	 
	foreach($html->find('li.g') as $g) {
		$h3 = $g->find('h3.r', 0);
		$s = $g->find('div.s', 0);
		if($h3 != ""){
			$a = $h3->find('a', 0);
		}
		$ris[] = array('title' => strip_tags($a->innertext), 
			'link' => $a->href, 
			'description' => strip_tags($s->innertext));
	}
	return $ris;
}

if($keyword) {
	if(is_array($keyword)) {
		$risultati = array();
		foreach($keyword as $k) {
			$test = estrapolazione($k);
			$risultati = array_merge($risultati,$test);
		}
	}
	else {
		$risultati = estrapolazione($keyword);
	}
}

// inizio output
foreach($risultati as $r) {
	$riga = '"' . $r["title"] . '","' . $r["description"] . "\"\n"; 
	file_put_contents( "risultati.csv",$riga,FILE_APPEND);
}
// fine output


$html->clear(); exit();
?>