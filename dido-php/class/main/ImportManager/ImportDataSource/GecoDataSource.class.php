<?php 
class GecoDataSource implements IExternalDataSource{
	
	const IMPORT_PATH = "geco-import/";

	private $_master_log;
	
	private $_tablesToRead = array (
				
			// Table Name => data
				
			"geco_missioni_dido" => array (
					"category" => "missioni",
					"id" => "id_missione",
					// xml pattern => coppia (chiave,valore) che lo identifica;
					// se xml pattern => valore allora è sempre quello.
					"xmlKeyPattern" => "missione",
					"xmlKeyType" => array (
							// tipo => coppia (chiave,valore) che lo identifica
							"con anticipo" => array(
									"anticipo" => 1
							),
							"senza anticipo" => array (
									"anticipo" => 0
							)
					)
	
			),
				
			"geco_ordini_dido" => array (
					"category" => "acquisti",
					"id" => "id_ordine",
					"xmlKeyPattern" => array (
							// xml pattern => coppia (chiave,valore) che lo identifica
							// xml pattern => true =  sempre
							"acquisto" => array	(
									"ordine_cassa" => 0
							),
							"ordine cassa" => array	(
									"ordine_cassa" => 1
							),
								
					),
					"xmlKeyType" => array (
							// tipo => coppia (chiave,valore) che lo identifica
							"mepa" => array(
									"ordine_mepa" => 1
							),
							"fuori mepa" => array (
									"ordine_mepa" => 0
							)
					)
			)
	);
	
	public function saveDataToBeImported(){
		$data_to_import = $this->getDataToImport();
		foreach ( $this->_tablesToRead as $table => $data ) {
			foreach ( $data_to_import [$data['category']] as $record ) {
				$result = $this->createFilesToImport ( $table, $data, $record );
				if(!$result){
					return false;
				}					
				
			}
		}
		//Per adesso non scriviamo mai l'esito
		//$this->_master_log->updateMasterLog("S", array_keys($this->_tablesToRead));
		
	}
	
	public function getSavedDataToBeImported($owner, $catlist){
		$fti = array("nTot" => 0);
			
		$types = glob(self::IMPORT_PATH ."*");
		
		foreach($types as $type){
			$needle = basename($type);
			if(in_array($needle,$catlist)){
		
				$files = glob($type."/*.");
				if(count($files)){
					foreach($files as $file){
						$filename = basename($file);
						preg_match_all(self::FILE_REGEXP, $filename,$matches);
						$fti[basename($type)][$matches[1][0]][] = array(
								'filename' 	=> basename($file),
								'md_nome'	=> $matches[1][0],
								'type'		=> $matches[2][0],
								'id'		=> $matches[3][0]
						);
					}
						
					$nTot = 0;
						
					foreach($fti[basename($type)] as $subType => $item){
						$nType = count($fti[basename($type)][$subType]);
						$nTot += $nType;
		
					}
						
					$fti[basename($type)]['nTot'] = $nTot;
					$fti['nTot'] += $nTot;
				}
			}
				
		}
		return $fti;
		
	}
	
	private function getDataToImport() {
		$data_to_import = array ();
		$this->_master_log = new master_log ();
	
		foreach ( $this->_tablesToRead as $table => $k ) {
			$lastId = $this->_master_log->getLastIdFlussoOk ( $table );
			$ormTable = new $table ();
			$recordsToImport = $ormTable->getRecordsToImport ( $lastId );
			$data_to_import [$k['category']] = $recordsToImport;
		}
	
		return $data_to_import;
	}
	
	private function createFilesToImport($table, $data, $record) {
		// Creiamo la direcrory in base alla categoria
		$dirname = self::IMPORT_PATH . $data['category'];
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
	
		$filename .= "_" . $record [$data['id']].".";
	
		$importfile = fopen ( $dirname . DIRECTORY_SEPARATOR . $filename, "w" ) or die ( "unable to open file" );
		
		if($importfile){
			fwrite ( $importfile, serialize($record ) );
			fclose ( $importfile );
			return true;
		}
		
		return false;
	}
}
?>