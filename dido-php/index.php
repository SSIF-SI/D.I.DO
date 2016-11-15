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

//$fcr = FlowChecker::getInstance()->checkMasterDocument(array('id_md' =>1));
//print_r(Personale::getInstance()->getPersonakey(),1);
//$md = new Masterdocument(Connector::getInstance());
/*

$result = null;
XMLParser::getInstance()->setXMLSource(XML_MD_PATH."missioni/missione.xml");

if(count($_POST) > 0){
	FormHelper::check($_POST, XMLParser::getInstance()->getMasterDocumentInputs());
	$result = FormHelper::getWarnBox();
}

$inputs = FormHelper::createInputsFromXml(XMLParser::getInstance()->getMasterDocumentInputs());
$perm=PermissionHelper::getInstance();

define("KARTIK_FILEINPUT", true);
*/
//Utils::printr(Personale::getInstance()->getPeopleByGroupType("Servizio"));

$pageScripts = array('datepicker.js');
include_once (TEMPLATES_PATH."template.php");
