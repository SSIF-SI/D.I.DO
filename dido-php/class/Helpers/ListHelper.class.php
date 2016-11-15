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
	
	static function listPersone(){
		return array_map(function($id){ return PersonaleHelper::getNominativo($id);}, Utils::getListfromField(Personale::getInstance()->getPersone(),'idPersona'));
	}
	
	static function listSigners(){
		$signersObj=new Signers(Connector::getInstance());
		$signers=$signersObj->getAll(null,"id_persona");
		$signers=array_map(function($id){ return PersonaleHelper::getNominativo($id);}, Utils::getListfromField($signers,'id_persona'));
		asort($signers);
		return $signers;
	}
	
	static function listProgetti($data = null){
		$list = Personale::getInstance()->getProgetti();
		if(!is_null($data)){
			$list= Utils::filterList($list, "inizio", $data, Utils::OP_LESS_THAN.Utils::OP_EQUAL);
			$list= Utils::filterList($list, "inizio", $data, Utils::OP_MORE_THAN.Utils::OP_EQUAL);
		}
		return $list;
	} 
}
?>