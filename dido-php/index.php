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

$result = null;
XMLParser::getInstance()->setXMLSource(XML_MD_PATH."missioni/missione.xml");

if(count($_POST) > 0){
	FormValidation::check($_POST, XMLParser::getInstance()->getMasterDocumentInputs());
	$result = FormValidation::getWarnBox();
}

foreach (XMLParser::getInstance()->getMasterDocumentInputs() as $input){
	$type = is_null($input['type']) ? 'text' : (string)$input['type'];
	$required = is_null($input['mandatory']) ? true : boolvar($input['mandatory']);
	$field = str_replace(" ", "_", (string)$input);
	$value = $_POST[$field];
	$warning = FormValidation::getWarnMessages($field);
	$class = isset($warning['class']) ? $warning['class'] : null; 
	$inputs[] = HTMLHelper::input($type, str_replace(" ", "_", (string)$input), (string)$input, $value, $class, false);
}

include_once (TEMPLATES_PATH."template.php");
