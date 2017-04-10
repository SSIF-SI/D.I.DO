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

//$fcr = FlowChecker::getInstance()->checkMasterDocument(array('id_md' =>53));
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
			$Importer = new Importer();
			$Importer->clean();
			$detail = TemplateHelper::createListGroupToImport();
			break;
		default:
		case 'documentOpen':
			$Responder = new Responder();
			$Responder->createDocList(true);
			
			$md_open = $Responder->getMyMasterDocuments();
			
			$detail = TemplateHelper::createListGroupOpen($md_open);
			break;
			$detail = null;
		
	}
	
}

if(isset($_GET['md'])){
	$FlowChecker = new FlowChecker();
	$fcr = $FlowChecker->checkMasterDocument(array('id_md' => $_GET['md']));
	
	if(isset($_GET['edit'])){
		if(isset($_GET['d'])){
			$docInputs = FlowChecker::getDocInputsByIdDoc($fcr, $_GET['d']);
			FormHelper::editInfo($_GET['d'], $_POST, $docInputs, $fcr['data']['info']['documents_data'][$_GET['d']], new DocumentData(Connector::getInstance()));
		} else {
			FormHelper::editInfo($_GET['md'], $_POST, $fcr['data']['xml_inputs'], $fcr['data']['info']['md_data'], new MasterdocumentData(Connector::getInstance()));
		}
		die();
	} else {
		$FlowChecker = new FlowChecker();
		$fcr = $FlowChecker->checkMasterDocument(array('id_md' => $_GET['md']));
		$detail = TemplateHelper::createTimeline($fcr);
	}
}

if(count($_POST) > 0){ // Importazione
	$Importer = new Importer();
	$Importer->import($_POST); 
}

$pageScripts = array('index.js','MyModal.js');
include_once (TEMPLATES_PATH."template.php");