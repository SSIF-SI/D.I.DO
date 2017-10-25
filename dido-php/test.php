<?php
require_once ("config.php");

$XMLParser = new XMLParser("pec/attività pec.xml");
$innerValues = $XMLParser->getMasterDocumentInnerValues();

$innerValuesToSearch = "ruoliAttivitaPec";
foreach ( $innerValues as $values ) {
	if ($values [XMLParser::IV_NAME] == $innerValuesToSearch) {
		$options = array();
		foreach($values as $value){
			$k = (string) $value[XMLParser::IV_VALUE];
			$v = (string) $value;
			$options[$k] = $v;
		}
		Utils::printr($options);
	}
}
