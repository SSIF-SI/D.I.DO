<?php 
$id_md = $_GET['md'];

$Application_Detail = $Application->getApplicationPart("Detail");

$md = $Application_DocumentBrowser->get($id_md);

if(!$md)
	Common::redirect();

//Utils::printr($md);

$Application_Detail->createDetail($md);
extract($md);

$view = basename(__FILE__);
?>