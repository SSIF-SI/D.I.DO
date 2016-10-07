<?php
class Personale {
	
	private static $_instance = null;
	private $_persone;
	private $_gruppi;
	
	private function __construct(){
		ini_set ( "soap.wsdl_cache_enabled", "0" );
		
		$wsdl_url = "http://pimpa.isti.cnr.it/PERSONALE/web-services/dido/dido.wsdl";
		$client = new SoapClient ( $wsdl_url );
			
		$personale = json_decode(json_encode($client->personale()),true);
		$gruppi = json_decode(json_encode($client->gruppi()),true);
		
		$this->_persone = Utils::getListfromField ( $personale, null, "idPersona");
		$this->_gruppi = Utils::getListfromField ( $gruppi, null, "sigla");
	}
	
	public static function getInstance() {
		if (self::$_instance == null) {
			self::$_instance = new Personale ();
		}
		return self::$_instance;
	}
	
	public function getPersone(){
		return $this->_persone;
	}
	
	public function getGruppi(){
		return $this->_gruppi;
	}
	
	public function getPersona($id){
		return $this->_persone[$id];
	}

	public function getGruppo($sigla){
		return $this->_gruppi[$sigla];
	}
	
}
?>