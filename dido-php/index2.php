<?php
require_once ("config.php");

$IM = new ImportManager(DBConnector::getInstance(), new FTPDataSource());

$XDS = new XMLDataSource();

$lastXML = $XDS
	->filter ( new XMLFilterDocumentType ( [ "acquisto" ] ) )
	->filter ( new XMLFilterValidity ( date ( "Y-m-d" ) ) )
	->getFirst ();

if (! $lastXML)
	die("Nessun xml");

$XmlParser = new XMLParser ( $lastXML [XMLDataSource::LABEL_XML] );

Utils::printr($IM->fromFileToMetadata(GecoDataSource::IMPORT_PATH."acquisti/acquisto_fuori mepa_1.tobeimported", $XmlParser->getMasterDocumentInputs()));
?>
