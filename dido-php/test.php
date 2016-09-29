<?php 

#DEBUG
ini_set('display_errors', 1);
error_reporting(E_ALL ^ E_DEPRECATED ^ E_NOTICE);

$xml = simplexml_load_file("test.xml");

function printr($txt){
	echo"<pre>".print_r($txt,1);
}

foreach($xml->list->document as $document){
	if(!is_null($document['load'])){
		$document = simplexml_load_file((string)XML_DEFAULT_DOC.$document['load']);
	}
	printr($document);
}
?>