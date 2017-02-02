<?php 
class PersonaleHelper{
	static function getNominativo($id){
		$result = Personale::getInstance()->getPersona($id)['cognome']." ".Personale::getInstance()->getPersona($id)['nome'];
		$result = trim($result);
		return empty($result) ? "<div class='alert alert-danger'>Utente non più attivo. Rimuovere.</div>": $result;
	}
}
?>