<?php
class FilterTypeDocument implements iFilterTypeSource{
	static function getTypes($ADB, $className, $types){
		$list = $ADB->searchDocuments($className, null, $types);
		if(!count($list[Application_DocumentBrowser::LABEL_DOCUMENTS]))
			die('<div class="alert alert-warning">Nessun tipo disponibile per la ricerca</div>');
		
		$types = array();
		
		foreach($list[Application_DocumentBrowser::LABEL_MD] as $md){
			$cat = reset(explode("/",$md[Masterdocument::XML]));
			if(!isset($types[$cat]))
				$types[$cat] = array();
			$docList = isset($list[Application_DocumentBrowser::LABEL_DOCUMENTS][$md[Masterdocument::ID_MD]]) ? $list[Application_DocumentBrowser::LABEL_DOCUMENTS][$md[Masterdocument::ID_MD]] : array();
			if(!empty($docList)) foreach($docList as $doc){
				$value = $doc[SharedDocumentConstants::NOME]. (isset($doc[SharedDocumentConstants::TYPE]) ? " - ".$doc[SharedDocumentConstants::TYPE] : "");
				if(!in_array($value, $types[$cat])) array_push($types[$cat],$value);
			}
			sort($types[$cat]);
		}
		
		ksort($types);
		
		return $types;
	}		
}