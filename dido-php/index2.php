<?php 
require_once ("config.php");

$Application = new Application();

if(count($_POST) > 0){
	$Application->importData($_POST);
	die();
}


?>