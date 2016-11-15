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
				//$record = Utils::getListfromField ( $record, $k['id'] );
				Utils::printr($record);
// 				self::createFilesToImport ( $table, $k['alias'], $record );
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
	private function getFileToImport() {
		// TODO
	}
	private function createFilesToImport($table, $alias, $record) {
		Utils::printr ( $record [$idToRead [$table]] );
		$filename = GECO_IMPORT_PATH . $alias . "/id" . $record [$idToRead [$table]];
		$dirname = GECO_IMPORT_PATH . $alias;
		if (! is_dir ( $dirname )) {
			mkdir ( $dirname, 0777, true );
		}
		$importfile = fopen ( $filename, "w" ) or die ( "unable to open file" );
		fwrite ( $importfile, join ( PHP_EOL, $record ) );
		fclose ( $importfile );
	}
}
?>