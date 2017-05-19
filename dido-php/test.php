<?php
require_once 'config.php';

$list = $Application
	->getApplicationPart(Application::DOCUMENTBROWSER)
	->getAllMyPendingsDocument();
		
$list[Application_DocumentBrowser::LABEL_MD] = Common::categorize($list[Application_DocumentBrowser::LABEL_MD]);

//Utils::printr($list[Application_DocumentBrowser::LABEL_MD]);

countItems($list, "missioni");

function countItems($array, $label){
	$array = findarray($array,$label);
	if(!$array) return 0;
	$sum=0;
	Utils::printr($array);
	$sum += countArray($array);
	Utils::printr($sum);
}

function findarray($array,$label){
	$retArr = false;
	if(array_key_exists($label, $array))
		return $array[$label];
	foreach($array as $key=>$values){
		if(is_array($values)){
			$retArr = findarray($values, $label);
			if($retArr)
				return $retArr;
		}
	}
	return false;
}

function countArray($array){
	if(count(array_filter(array_keys($array), 'is_string')) == 0) 
		return count($array);
	$sum = 0;
	foreach($array as $key=>$values){
		$sum += countArray($values);
	}
	return $sum;
}