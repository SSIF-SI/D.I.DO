<?php 
class ImportHelper{
	static function idPersonaFromCf($cf){
		return Personale::getInstance()->getPersonabyCf($cf)['id'];
	}
	
	static function gruppoFromCf($cf){
		$gruppi = Personale::getInstance()->getGruppi();
		$gruppo = Utils::filterList($gruppi, "cf_responsabile", $cf);
		return $gruppo['id_responsabile'];
	}
}
?>