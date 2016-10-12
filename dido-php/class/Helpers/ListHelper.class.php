<?php 
class ListHelper{
	static function gruppi(){
		$gruppi = Personale::getInstance()->getGruppi();
		foreach($gruppi as $k=>$dati){
			$gruppi[$k]['descrizione'] = $gruppi[$k]['sigla']." (". $gruppi[$k]['descrizione'].")";
		}
		return Utils::getListfromField($gruppi,'descrizione');
	}
		
	static function titolariFondi(){
		return array();
	}
	
	static function responsabili(){
		$persone = Personale::getInstance()->getPersone();
		$id_responsabili = Utils::getListfromField(Personale::getInstance()->getGruppi(),'id_responsabile');
		foreach($persone as $id=>$persona){
			if(!in_array($id, $id_responsabili))
				unset($persone[$id]);
			else {
				$gruppo = array_search($id, $id_responsabili);
				$persone[$id]['label'] = $persone[$id]['nome']." ".$persone[$id]['cognome']. " ($gruppo)";
			}
		}
		return Utils::getListfromField($persone,'label');
	}
}
?>