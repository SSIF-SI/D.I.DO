<?php
require_once ("../config.php");
$signature=new Signature(Connector::getInstance());
$allsignatures=$signature->getAll("sigla","id_persona");
$arrayk= array_keys(reset($allsignatures));

$pageScripts = array('simpleDataTable.js');

include_once (TEMPLATES_PATH."template.php");