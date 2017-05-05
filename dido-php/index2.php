<?php 
require_once ("config.php");

$Application = new Application();

Utils::printr($Application->getSavedDataToBeImported());


?>