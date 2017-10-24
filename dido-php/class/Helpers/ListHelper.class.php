<?php

class ListHelper {

	static function gruppi() {
		$gruppi = Personale::getInstance ()->getGruppi ();
		foreach ( $gruppi as $k => $dati ) {
			$gruppi [$k] ['descrizione'] = $gruppi [$k] ['sigla'] . " (" . $gruppi [$k] ['descrizione'] . ")";
		}
		return Utils::getListfromField ( $gruppi, 'descrizione' );
	}

	static function titolariFondi() {
		return self::responsabili ();
	}

	static function responsabili() {
		$persone = Personale::getInstance ()->getPersone ();
		$id_responsabili = Utils::getListfromField ( Personale::getInstance ()->getGruppi (), 'id_responsabile' );
		foreach ( $persone as $id => $persona ) {
			if (! in_array ( $id, $id_responsabili ))
				unset ( $persone [$id] );
			else {
				$gruppo = array_search ( $id, $id_responsabili );
				$persone [$id] ['label'] = $persone [$id] ['nome'] . " " . $persone [$id] ['cognome'] . " ($gruppo)";
			}
		}
		return Utils::getListfromField ( $persone, 'label' );
	}

	static function persone() {
		return array_map ( function ($id) {
			return Personale::getInstance()->getNominativo ( $id );
		}, Utils::getListfromField ( Personale::getInstance ()->getPersonale (), Personale::ID_PERSONA ) );
	}

	static function signers() {
		$signersObj = new Signers ( DBConnector::getInstance () );
		$signers = $signersObj->getAll ( null, "id_persona" );
		$signers = array_map ( function ($id) {
			return PersonaleHelper::getNominativo ( $id );
		}, Utils::getListfromField ( $signers, 'id_persona' ) );
		asort ( $signers );
		return $signers;
	}

	static function progetti($data = null) {
		$list = Personale::getInstance ()->getProgetti ();
		if (! is_null ( $data )) {
			$list = Utils::filterList ( $list, "inizio", $data, Utils::OP_LESS_THAN . Utils::OP_EQUAL );
			$list = Utils::filterList ( $list, "inizio", $data, Utils::OP_MORE_THAN . Utils::OP_EQUAL );
		}
		$list = Utils::getListfromField($list, "acronimo", "id");
		return $list;
	}

	static function tipiElaborazione() {
		return array (
				"cassa" => "Cassa",
				"competenza" => "Competenza" 
		);
	}

	static function areeProgettuali() {
		$areeObj= new aree_progettuali();
		$aree=$areeObj->getStub();
		finfo("Aree: %o",$aree);
	}

	static function esitiRichiesteDiDelega() {
		return array (
				"accolta" => "Accolta",
				"negata" => "Negata",
				"parziale" => "Parziale" 
		);
	}

	static function ruoliAttivitaPec() {
		return array (
				"coordinatore" => "Coordinatore",
				"partner" => "Partner",
				"contraente" => "Contraente",
				"subcontraente" => "Subcontraente" 
		);
	}

	static function esitiAttivitaPec() {
		return array (
				"ammessa" => "Ammessa",
				"non ammessa" => "Non ammessa",
				"sospesa" => "Sospesa" 
		);
	}
}
?>