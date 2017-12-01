<?php
class FilterTypeMasterdocument implements iFilterTypeSource{
	static function getTypes($ADB, $className, $types){
		$list = $ADB->searchDocuments($className, null, $types);
		
		if(!count($list[Application_DocumentBrowser::LABEL_MD]))
			die('<div class="alert alert-warning">Nessun tipo disponibile per la ricerca</div>');
		
		$types = array();
		
		foreach($list[Application_DocumentBrowser::LABEL_MD] as $md){
			$cat = reset(explode("/",$md[Masterdocument::XML]));
			if(!isset($types[$cat]))
				$types[$cat] = array();
			$value = $md[SharedDocumentConstants::NOME]. (isset($md[SharedDocumentConstants::TYPE]) ? " - ".$md[SharedDocumentConstants::TYPE] : "");
			if(!in_array($value, $types[$cat])) array_push($types[$cat],$value);
		}
		
		foreach($types as $cat=>$list)
			sort ($types[$cat]);
		
		ksort($types);
		return $types;
	}		
}