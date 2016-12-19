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
			
			$MasterDocument = new Masterdocument(Connector::getInstance());
			$md_open = Utils::getListfromField($MasterDocument->getBy("closed", "0", "nome, type"), null, "id_md");
			
			$MasterDocumentData = new MasterdocumentData(Connector::getInstance());
			$md_data = Utils::groupListBy($MasterDocumentData->getBy("id_md", join(",",array_keys($md_open))), "id_md");
				
			$xmlList = XMLBrowser::getInstance()->getXmlList( false );
			
			foreach($md_open as $k => $md){
				if(!array_key_exists($md['xml'], $xmlList)) unset($md_open[$k]);
			}
			
			
			$detail = Utils::printr($md_open, true);
			$detail .= Utils::printr($md_data, true);
			break;
			$detail = null;
		
	}
	
}

if(count($_POST) > 0){ // Importazione
	$Importer = new Importer();
	$Importer->import($_POST); 
}

$pageScripts = array('index.js','MyModal.js');
include_once (TEMPLATES_PATH."template.php");
