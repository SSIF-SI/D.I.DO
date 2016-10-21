<?php 
class PersonaleHelper{
	static function getNominativo($id){
		return Personale::getInstance()->getPersona($id)['nome']." ".Personale::getInstance()->getPersona($id)['cognome'];
	}
}
?>