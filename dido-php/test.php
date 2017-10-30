<?php 

require_once("config.php");

$XS = new XMLDataSource();

$XS->filter(new XMLFilterFilename(array('contratti/compenso.xml','pec/richiesta di delega.xml')));
$tree = $XS->getXmlTree(true);
Utils::printr($tree);

$XMLParser = new XMLParser();
foreach($tree as $fName){
	$xml = $XS->getSingleXmlByFilename($fName);	
	$XMLParser->setXMLSource($xml["xml"]);
	
	$inputs = $XMLParser->getMasterDocumentInputs();
	$doc_list=$XMLParser->getDocList();

// 	$doc_inputs=$XMLParser->getDocList()
	foreach ($inputs as $input){
		Utils::printr("MasterDocument -> $input -> ".(isset($input[XMLParser::TYPE]) ? $input[XMLParser::TYPE] : "Stringa"));
		Utils::printr("MasterDocument -> $input -> ".(isset($input[XMLParser::VALUES]) ? $input[XMLParser::VALUES] : "no transform"));
		
	}
	foreach ($doc_list as $doc){
		$inputs=$XMLParser->getDocumentInputs($doc[XMLParser::DOC_NAME]);
		foreach ($inputs as $input){
			Utils::printr("Document -> $input-> ".(isset($input[XMLParser::TYPE]) ? $input[XMLParser::TYPE] : "Stringa"));
			Utils::printr("Document -> $input-> ".(isset($input[XMLParser::VALUES]) ? $input[XMLParser::VALUES] : "no transform"));
				
		}
	}
	
}



?>
