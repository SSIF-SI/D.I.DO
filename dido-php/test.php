<?php 
require_once 'config.php';

$PH = PermissionHelper::getInstance();
Utils::printr(var_dump($PH->getUser()));
?>