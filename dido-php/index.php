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
XMLParser::getInstance()->setXMLSource(XmlBrowser::getInstance()->getgetSingleXml("missioni/missione.v01.xml"));

if(count($_POST) > 0){
	FormHelper::check($_POST, XMLParser::getInstance()->getMasterDocumentInputs());
	$result = FormHelper::getWarnBox();
}

$inputs = FormHelper::createInputsFromXml(XMLParser::getInstance()->getMasterDocumentInputs());
$perm=PermissionHelper::getInstance();

define("KARTIK_FILEINPUT", true);
*/
//Utils::printr(Personale::getInstance()->getPeopleByGroupType("Servizio"));


if( isset($_GET['detail'])){ 
	switch($_GET['detail']){
		case 'documentToImport':
			$detail = TemplateHelper::createListGroupToImport();
			break;
		default:
			$detail = null;
		
	}
	
}

if(count($_POST) > 0){ // Importazione
	die(json_encode(array('errors' => false)));
}

$pageScripts = array('index.js','MyModal.js');
include_once (TEMPLATES_PATH."template.php");
