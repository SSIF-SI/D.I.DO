<?php 

require_once("config.php");

$XS = new XMLDataSource();

$XS->filter(new XMLFilterFilename(array('contratti/compenso.xml','pec/richiesta di delega.xml')));
$tree = $XS->getXmlTree(true);
Utils::printr($tree);

$XMLParser = new XMLParser();
foreach($tree as $fName){
	$xml = $XS->getSingleXmlByFilename($fName);
	$XMLParser->setXMLSource($xml);
	$inputs = $XMLParser->getMasterDocumentInputs();
	foreach ($inputs as $input){
		Utils::printr("$input -> ".(isset($input[XMLParser::TYPE]) ? $input[XMLParser::TYPE] : "Stringa"));
	}
	
}



?>
