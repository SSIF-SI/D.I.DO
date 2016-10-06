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
			$arraypersonale = json_decode(json_encode($client->personale()),true);
				
			$personaKey = Utils::getListfromField ( $arraypersonale, "email");
			echo"<pre>".print_r($personaKey,1)."</pre>";
				
			
// 			$groups = Utils::getListfromField ( $client->gruppi (), null, "codiceFiscale" );
		}
		return self::$instance;
	}
	
	public function getEmail($idpersona) {
		$persona = $personakey [$idpersona];
		return $persona ["email"];
	}
	public function getPersonakey() {
		return $personakey;
	}
}
?>