<?php
class Personale {
	
	private static $instance = null;
	private $wsdl_url;
	private $client;
	private $personakey;
	private $groups;
	
	public static function getInstance() {
		if (self::$instance == null) {
			self::$instance = new Personale ();
			ini_set ( "soap.wsdl_cache_enabled", "0" );
			$wsdl_url = "http://pimpa.isti.cnr.it/PERSONALE/web-services/dido/dido.wsdl";
			$client = new SoapClient ( $wsdl_url );
			$personaKey = Utils::getListfromField ( $client->personale (), null, "idpersona" );
			$groups = Utils::getListfromField ( $client->gruppi (), null, "idpersona" );
		}
		return self::$instance;
	}
	
	public function getEmail($idpersona) {
		$persona = $personakey [$idpersona];
		return $persona ["email"];
	}
}
?>