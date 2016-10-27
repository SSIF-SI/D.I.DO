<?php
require_once ("../config.php");

if (Utils::checkAjax ())
	die("Loaded with Ajax");

$pageScripts = array (
	"MyModal.js", "testModal.js"
);
include_once (TEMPLATES_PATH . "template.php");