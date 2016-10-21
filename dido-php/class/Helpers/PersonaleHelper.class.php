<?php 
class PersonaleHelper{
	static function getNominativo($id){
		return Personale::getInstance()->getPersona($id)['cognome']." ".Personale::getInstance()->getPersona($id)['nome'];
	}
}
?>