<?php
require_once 'config.php';
/*
 * XMLParser::getInstance()->setXMLSource(XmlBrowser::getInstance()->getSingleXml("missioni/missione.v01.xml"));
 *
 * if(count($_POST) > 0){
 * FormHelper::check($_POST,
 * XMLParser::getInstance()->getMasterDocumentInputs());
 * $result = FormHelper::getWarnBox();
 * }
 *
 * $_POST['id_missione'] = 123;
 * $inputs =
 * FormHelper::createInputsFromXml(XMLParser::getInstance()->getMasterDocumentInputs(),null,$_IMPORT);
 */

Geko::getInstance ()->importFromSI ();
die ();
$pageScripts = array (
		'datepicker.js' 
);
include_once (TEMPLATES_PATH . "template.php");

?>