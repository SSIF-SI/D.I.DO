<?php

class ImportHelper {

	static function idPersonaFromCf($cf) {
		return Personale::getInstance ()->getPersonabyCf ( $cf )['idPersona'];
	}

	static function gruppoFromCf($cf) {
		$gruppi = Personale::getInstance ()->getGruppi ();
		$gruppo = Utils::filterList ( $gruppi, "cf_responsabile", $cf );
		$gruppo = reset ( $gruppo );
		return $gruppo ['sigla'];
	}
}
?>