<?php 
require_once ("../config.php");
define (PAGE_TITLE, "Ricerca");

$Search = new Search();

$pageScripts = array (
		"MyModal.js"
);

include_once (TEMPLATES_PATH . "template.php");

?>