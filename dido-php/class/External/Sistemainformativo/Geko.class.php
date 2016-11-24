<?php
class Geko extends ClassWithDependencies{
	private static $FILE_REGEXP = "/([a-zA-Z\s]{1,})_([a-zA-Z\s]{0,})_{0,1}([0-9]{1,})/";
	
	private $_PermissionHelper;
	private $_XMLBrowser;
	
	private static $_instance = null;
	
	private $_tablesToRead = array (
			
			// Table Name => data
			
			"geco_missioni_dido" => array (
				"category" => "missioni",
				"id" => "id_missione",
				// xml pattern => coppia (chiave,valore) che lo identifica;
				// se xml pattern => valore allora è sempre quello.
				"xmlKeyPattern" => "missione"
			),
			
			"geco_ordini_dido" => array (
				"category" => "acquisti",
				"id" => "id_ordne",
				"xmlKeyPattern" => array (
					// xml pattern => coppia (chiave,valore) che lo identifica
					// xml pattern => true =  sempre
					"acquisto" => array	(
						"ordine_cassa" => "n"	
					),
					"ordine cassa" => array	(
						"ordine_cassa" => "s"
					),
					
				),
				"xmlKeyType" => array (
					// tipo => coppia (chiave,valore) che lo identifica
					"mepa" => array(
						"ordine_mepa" => "s"
					),
					"fuori mepa" => array (
						"ordine_mepa" => "n"
					)
				)
			) 
	);
	
	private function __construct() {
		$this->_PermissionHelper = PermissionHelper::getInstance();
		$this->_XMLBrowser = XMLBrowser::getInstance();
	}
	
	private function __clone(){}
	
	private function __wakeup(){}
	
	public static function getInstance() {
		if (self::$_instance == null) {
			self::$_instance = new self ();
		}
		return self::$_instance;
	}
	
	public function importFromSI() {
		$data_to_import = self::getDataToImport ();
		
		foreach ( $this->_tablesToRead as $table => $data ) {
			foreach ( $data_to_import [$data['category']] as $record ) {
				self::createFilesToImport ( $table, $data, $record );
			}
		}
	}
	
	public function CreateMasterDocumentFromFile($filename) {
		// TODO
	}
	
	private function getDataToImport() {
		$data_to_import = array ();
		$ml = new master_log ();
		
		foreach ( $this->_tablesToRead as $table => $k ) {
			$lastId = $ml->getLastIdFlussoOk ( $table );
			$ormTable = new $table ();
			$recordsToImport = $ormTable->getRecordsToImport ( $lastId );
			$data_to_import [$k['category']] = $recordsToImport;
		}
		
		return $data_to_import;
	}
	
	public function getFileToImport() {
		
		$owner = $this->_PermissionHelper->isAdmin() ? null : $this->_PermissionHelper->getUserField("gruppi");
		
		
		$catlist = array_keys($this->_XMLBrowser->getXmlListByOwner($owner));
		$fti = array("nTot" => 0);
		
		$types = glob(GECO_IMPORT_PATH."*");
		foreach($types as $type){
			$needle = basename($type);
			
			if(in_array($needle,$catlist)){
					
				$files = glob($type."/*");
				if(count($files)){
					foreach($files as $file){
						$filename = basename($file);
						preg_match_all(self::$FILE_REGEXP, $filename,$matches);
						$fti[basename($type)][] = array(
							'filename' 	=> basename($file),
							'xml'		=> $matches[1],
							'type'		=> $matches[2],
							'id'		=> $matches[3]
						);
					}
					$fti['nTot'] += count($fti[basename($type)]);
				}
			}
			
		}
		return $fti;
	}
	
	private function createFilesToImport($table, $data, $record) {
		// Creiamo la direcrory in base alla categoria
		$dirname = GECO_IMPORT_PATH . $data['category'];
		if (! is_dir ( $dirname )) {
			mkdir ( $dirname, 0777, true );
		}
		
		// Il nome del file dovrà essere descrittivo e allineato con i nomi degli xml
		// pattern: <xml>_<type>_<id>
		// es: acquisto_fuori mepa_1
		// Il tipo non è obbligatorio
		// es: missione_2
				
		$filename = array();
		
		if(isset($data['xmlKeyPattern'])){
			if(!is_array($data['xmlKeyPattern']))
				$filename[] = $data['xmlKeyPattern'];
			else {
				foreach($data['xmlKeyPattern'] as $fnToAppend => $onlyIf){
					foreach($onlyIf as $assert_key=>$assert_value){
						if(!isset($record[$assert_key]))
							continue;
						if($record[$assert_key] == $assert_value) $filename[] = $fnToAppend;
					}
				}
			}
		}
		
		
		$filename = join("_",$filename);
		if(empty($filename)) $filename = "????";

		if(isset($data['xmlKeyType'])){
			foreach($data['xmlKeyType'] as $fnToAppend => $onlyIf){
				foreach($onlyIf as $assert_key=>$assert_value){
					if(!isset($record[$assert_key])) continue;
					if($record[$assert_key] == $assert_value) $filename .= "_$fnToAppend";
				}
			}
			
		}
		
		$filename .= "_" . $record [$data['id']];
		
		Utils::printr($filename);
		
		$importfile = fopen ( $dirname . DIRECTORY_SEPARATOR . $filename, "w" ) or die ( "unable to open file" );
		fwrite ( $importfile, serialize($record ) );
		fclose ( $importfile );
	}
}
?>