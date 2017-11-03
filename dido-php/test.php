<?php 

require_once("config.php");

// $XS = new XMLDataSource();

// $XS->filter(new XMLFilterFilename(array('contratti/compenso.xml','pec/richiesta di delega.xml')));
// $tree = $XS->getXmlTree(true);
// Utils::printr($tree);

// $XMLParser = new XMLParser();

// foreach($tree as $fName){
// 	$xml = $XS->getSingleXmlByFilename($fName);	
// 	$XMLParser->setXMLSource($xml["xml"]);
	
// 	$inputs = $XMLParser->getMasterDocumentInputs();
// 	$doc_list=$XMLParser->getDocList();

// // 	$doc_inputs=$XMLParser->getDocList()
// 	foreach ($inputs as $input){
// 		Utils::printr("MasterDocument -> $input -> ".(isset($input[XMLParser::TYPE]) ? $input[XMLParser::TYPE] : "Stringa"));
// 		Utils::printr("MasterDocument -> $input -> ".(isset($input[XMLParser::VALUES]) ? $input[XMLParser::VALUES] : "no transform"));
		
// 	}
// 	foreach ($doc_list as $doc){
// 		$inputs=$XMLParser->getDocumentInputs($doc[XMLParser::DOC_NAME]);
// 		foreach ($inputs as $input){
// 			Utils::printr("Document -> $input-> ".(isset($input[XMLParser::TYPE]) ? $input[XMLParser::TYPE] : "Stringa"));
// 			Utils::printr("Document -> $input-> ".(isset($input[XMLParser::VALUES]) ? $input[XMLParser::VALUES] : "no transform"));
				
// 		}
// 	}
	
// }

$className = "Masterdocument";
$dataclassName = $className . "Data";

$A = new Application ();
$D = new $dataclassName ( $A->getDBConnector () );
$D->useView ( true );
	$where=$dataclassName::VALUE." ilike '%z%'";
	$sql="SELECT distinct %s FROM %s WHERE %s ORDER BY %s";
	$sql = sprintf($sql, $dataclassName::VALUE." , ".$dataclassName::KEY, "search_master_documents_data_view", $where, $dataclassName::VALUE);
	
Utils::printr( $sql );
$listkeyValues= $D->getRealDistinct ( $dataclassName::VALUE." , ".$dataclassName::KEY, $where, $dataclassName::VALUE );
Utils::printr($listkeyValues);
$listkeyValues = Utils::getListfromField ( $listkeyValues, $dataclassName::VALUE, $dataclassName::KEY );




?>
