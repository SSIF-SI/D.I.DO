<?php 
require("../config.php");
if(!Utils::checkAjax()) die();

Utils::printr($_REQUEST);

?>