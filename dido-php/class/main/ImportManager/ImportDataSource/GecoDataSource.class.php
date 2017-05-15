<?php

class GecoDataSource implements IExternalDataSource {
	const DATA_SOURCE_LABEL = "geco";
	const IMPORT_PATH = "geco-import/";

	const DOCUMENT_FIELD = "nome_documento";
	
	private $_master_log;
	
	private $_ftpConnector;

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
							"con anticipo" => array (
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
							// xml pattern => coppia (chiave,valore) che lo
							// identifica
							// xml pattern => true = sempre
							"acquisto" => array (
									"ordine_cassa" => 0 
							),
							"ordine cassa" => array (
									"ordine_cassa" => 1 
							) 
					),
					"xmlKeyType" => array (
							// tipo => coppia (chiave,valore) che lo identifica
							"mepa" => array (
									"ordine_mepa" => 1 
							),
							"fuori mepa" => array (
									"ordine_mepa" => 0 
							) 
					) 
			) 
	);

	public function getExternalDocument($destinationPath, $record){
		return $this->_getExternalDocumentFromFTP($destinationPath, $record);
	}
	
	private function _getExternalDocumentFromFTP($destinationPath, $record){
		$FTPConfiguratorSource = new FTPConfiguratorSourceFromIniFile("config.ini");
		$this->_ftpConnector = new FTPConnector();
		$nomeFile = self::IMPORT_PATH . $record[self::DOCUMENT_FIELD];
		return $this->_ftpConnector->getTempFile($nomeFile, $destinationPath);
	}
	
	public function saveDataToBeImported() {
		
		$data_to_import = $this->getDataToImport ();
		foreach ( $this->_tablesToRead as $table => $data ) {
			foreach ( $data_to_import [$data ['category']] as $record ) {
				$result = $this->createFileToImport ( $table, $data, $record );
				if (! $result) {
					return false;
				}
			}
		}
		return true;
		// Per adesso non scriviamo mai l'esito
		// $this->_master_log->updateMasterLog("S",
		// array_keys($this->_tablesToRead));
	}

	public function getSavedDataToBeImported($owner, $catlist, $subcategory = null) {
		$fti = [];
		
		$types = glob ( self::IMPORT_PATH . "*" );
		
		$fti[self::FILE_EXTENSION_TO_BE_IMPORTED] = $this->_browse(self::FILE_EXTENSION_TO_BE_IMPORTED, $types, $catlist);
		$fti[self::FILE_EXTENSION_TO_BE_UPDATED] = $this->_browse(self::FILE_EXTENSION_TO_BE_UPDATED, $types, $catlist);
		
		return is_null($subcategory) ? $fti : $fti[$subcategory];
	}

	private function _browse($label, $types, $catlist){
		$found = 0;
		$return = [Common::N_TOT => 0];
		foreach ( $types as $type ) {
			$needle = basename ( $type );
			if (in_array ( $needle, $catlist )) {
				$list = [Common::N_TOT => 0];	
				$files = glob ( $type . "/*." . $label );
				if (count ( $files )) {
					foreach ( $files as $file ) {
						$filename = basename ( $file );
						preg_match_all ( self::FILE_REGEXP, $filename, $matches );
						$list [$matches [1] [0]] [] = array (
								self::FILENAME => $file ,
								self::MD_NOME => $matches [1] [0],
								self::TYPE => $matches [2] [0],
								self::ID => $matches [3] [0]
						);
						$list [Common::N_TOT] ++;
							
					}
						
					$return[Common::N_TOT] += $list [Common::N_TOT];
				}
			}
			$return[$needle] = $list;
		}
		return $return;
			
	}
	private function getDataToImport() {
		$data_to_import = array ();
		$this->_master_log = new master_log ();
		
		foreach ( $this->_tablesToRead as $table => $k ) {
			$lastId = $this->_master_log->getLastIdFlussoOk ( $table );
			$ormTable = new $table ();
			$recordsToImport = $ormTable->getRecordsToImport ( $lastId );
			$data_to_import [$k ['category']] = $recordsToImport;
		}
		
		return $data_to_import;
	}

	private function createFileToImport($table, $data, $record) {
		// Creiamo la direcrory in base alla categoria
		$dirname = self::IMPORT_PATH . $data ['category'];
		if (! is_dir ( $dirname )) {
			mkdir ( $dirname, 0777, true );
		}
		
		// Il nome del file dovrà essere descrittivo e allineato con i nomi
		// degli xml
		// pattern: <xml>_<type>_<id>
		// es: acquisto_fuori mepa_1
		// Il tipo non è obbligatorio
		// es: missione_2
		
		$filename = array ();
		
		if (isset ( $data ['xmlKeyPattern'] )) {
			if (! is_array ( $data ['xmlKeyPattern'] ))
				$filename [] = $data ['xmlKeyPattern'];
			else {
				foreach ( $data ['xmlKeyPattern'] as $fnToAppend => $onlyIf ) {
					foreach ( $onlyIf as $assert_key => $assert_value ) {
						if (! isset ( $record [$assert_key] ))
							continue;
						if ($record [$assert_key] == $assert_value)
							$filename [] = $fnToAppend;
					}
				}
			}
		}
		
		$filename = join ( "_", $filename );
		if (empty ( $filename ))
			$filename = "????";
		
		if (isset ( $data ['xmlKeyType'] )) {
			foreach ( $data ['xmlKeyType'] as $fnToAppend => $onlyIf ) {
				foreach ( $onlyIf as $assert_key => $assert_value ) {
					if (! isset ( $record [$assert_key] ))
						continue;
					if ($record [$assert_key] == $assert_value)
						$filename .= "_$fnToAppend";
				}
			}
		}
		
		$folder = $dirname . DIRECTORY_SEPARATOR;
		$filename .= "_" . $record [$data [self::ID]];
		
		$extension = 
			file_exists($folder.$filename.".".self::FILE_EXTENSION_IMPORTED) ?
			"." . self::FILE_EXTENSION_TO_BE_UPDATED :
			"." . self::FILE_EXTENSION_TO_BE_IMPORTED;
		
		
		$importfile = @fopen ( $folder . $filename . $extension, "w" );
		
		if ($importfile) {
			fwrite ( $importfile, serialize ( $record ) );
			fclose ( $importfile );
			return true;
		}
		
		return false;
	}
}
?>