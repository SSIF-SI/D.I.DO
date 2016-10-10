<?php 

require_once ("config.php");

/*
$PDFParser = new PDFParser("/var/lib/tomcat7/webapps/dido-php-test/richiesta_delega_2016_DMT_signed.pdf");
echo Utils::printr($PDFParser->getSignatures());
echo Utils::printr($PDFParser->getMetadata());
var_dump($PDFParser->isPDFA());
*/
/*
$ftp = FTPConnector::getInstance();
$contents = $ftp->getContents("/CAMPUS");
Utils::printr(Utils::filterList($contents['contents'],'isPDF',1));
*/
/*
Utils::printr(Personale::getInstance()->getPersone());
Utils::printr(Personale::getInstance()->getGruppi());
*/

// FlowChecker::getInstance()->checkMasterDocument(array('id_md' =>1));
// print_r(Personale::getInstance()->getPersonakey(),1);
// $md = new Masterdocument(Connector::getInstance());


XMLParser::getInstance()->setXMLSource(XML_MD_PATH."missioni/missione.xml");
foreach (XMLParser::getInstance()->getMasterDocumentInputs() as $input){
	$type = is_null($input['type']) ? 'text' : $type;
	$inputs[] = HTMLHelper::input($type, str_replace(" ", "_", (string)$input), (string)$input, $_POST[str_replace(" ", "_", (string)$input)], check($_POST[str_replace(" ", "_", (string)$input)]));
}

$inputs = join("<br>",$inputs);
$result = count($_POST) > 0 ? "<pre>".print_r($_POST,1)."</pre>" : "";

include_once (TEMPLATES_PATH."template.php");

function check($var){
	if(count($_POST)>0){
		$var = trim($var);
		return $var === "" ? 'has-error' : '';
	}
	return null;
}