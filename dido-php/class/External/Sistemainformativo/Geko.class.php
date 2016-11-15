<?php 
class Geko{
	private static $_instance = null;
	private $_connection;
	private static $tablesToRead = array(
		// Table Name 			=> Alias
		"geco_missioni_dido" 	=> "Missioni",
		"geco_ordini_dido" 		=> "Ordini"
	);
	
	private function __construct(){}

	public static function getInstance() {
		if (self::$_instance == null) {
			self::$_instance = new Geko ();
		}
		return self::$_instance;
	}
	
	public function importFromSI(){
		// TODO
	}
	
	public function CreateMasterDocumentFromFile($filename){
		// TODO
	}
	
	
	
	public function getDataToImport(){
		$data_to_import = array();
		$ml = new master_log();
		
		foreach(self::$tablesToRead as $table=>$alias){
			$lastId = $ml->getLastIdFlussoOk($table);
			$ormTable = new $table();
			$recordsToImport = $ormTable->getRecordsToImport($lastId);
			$data_to_import[$alias] = $recordsToImport;		
		}
		
		return $data_to_import;
	}
}
?>