<?php 
$id_md = $_GET[Masterdocument::ID_MD];
$Application_Detail = $Application->getApplicationPart("Detail");
$md = $Application_DocumentBrowser->get($id_md);
$mdLinks = $Application_DocumentBrowser->getLinkedMd($id_md);

if(!$md || empty($md[Application_DocumentBrowser::LABEL_MD]))
	Common::redirect();

$Application_Detail->createDetail($md, $mdLinks);
extract($md);

$view = basename(__FILE__);
?>