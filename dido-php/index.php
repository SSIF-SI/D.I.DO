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
FlowChecker::getInstance()->checkMasterDocument(array('id_md' =>1));
// print_r(Personale::getInstance()->getPersonakey(),1);
//$md = new Masterdocument(Connector::getInstance());
