<?php 
$id_md = $_GET['md'];

$Application_Detail = $Application->getApplicationPart("Detail");

$md = $Application_DocumentBrowser->get($id_md);

if(!$md)
	Common::redirect();

$Application_Detail->createDetail($md);
extract($md);

$view = basename(__FILE__);
$pageScripts = array("MyModal.js");
include_once (TEMPLATES_PATH."template.php");
?>