<?php
class Geko {
	private static $_instance = null;
	private $_connection;
	private static $tablesToRead = array (
			// Table Name => Alias
			"geco_missioni_dido" => array (
					"alias" => "missioni",
					"id" => "id_missione" 
			),
			"geco_ordini_dido" => array (
					"alias" => "ordini",
					"id" => "id_ordne" 
			) 
	);
	private function __construct() {
	}
	public static function getInstance() {
		if (self::$_instance == null) {
			self::$_instance = new Geko ();
		}
		return self::$_instance;
	}
	public function importFromSI() {
		$data_to_import = self::getDataToImport ();
		
		foreach ( self::$tablesToRead as $table => $k ) {
			foreach ( $data_to_import [$k['alias']] as $record ) {
				self::createFilesToImport ( $table, $k, $record );
			}
		}
	}
	public function CreateMasterDocumentFromFile($filename) {
		// TODO
	}
	private function getDataToImport() {
		$data_to_import = array ();
		$ml = new master_log ();
		
		foreach ( self::$tablesToRead as $table => $k ) {
			$lastId = $ml->getLastIdFlussoOk ( $table );
			$ormTable = new $table ();
			$recordsToImport = $ormTable->getRecordsToImport ( $lastId );
			$data_to_import [$k['alias']] = $recordsToImport;
		}
		
		return $data_to_import;
	}
	
	public function getFileToImport($type = null) {
		$fti = array("nTot" => 0);
		if(is_null($type)){
			$types = glob(GECO_IMPORT_PATH."*");
			foreach($types as $type){
				$files = glob($type."/*");
				if(count($files)){
					foreach($files as $file)
						$fti[basename($type)][] = basename($file);
					$fti['nTot'] += count($fti[basename($type)]);
				}
			}
		}
		return $fti;
	}
	
	private function createFilesToImport($table, $k, $record) {
		$filename = $record [$k['id']];
		$dirname = GECO_IMPORT_PATH . $k['alias'];
		if (! is_dir ( $dirname )) {
			mkdir ( $dirname, 0777, true );
		}
		$importfile = fopen ( $dirname . DIRECTORY_SEPARATOR . $filename, "w" ) or die ( "unable to open file" );
		fwrite ( $importfile, serialize($record ) );
		fclose ( $importfile );
	}
}
?>