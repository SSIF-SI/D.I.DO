<?php

class ImportManager {

	const LABEL_IMPORT_FILENAME = "import_filename";

	const LABEL_MD_NOME = "md_nome";

	const LABEL_MD_TYPE = "md_type";
	
	const LABEL_MD_XML = "xml";

	const FAKEFILE = "fakefile/sample06.pdf";

	private $_dbConnector;

	private $_ftpDataSource;
	
	private $_importDataSourceManager;

	private $_ProcedureManager;

	public function __construct(IDBConnector $dbConnector, IFTPDataSource $ftpDataSource) {
		$this->_dbConnector = $dbConnector;
		$this->_ftpDataSource = $ftpDataSource;
		$this->_importDataSourceManager = new ImportDataSourceManager ();
		$this->_ProcedureManager = new ProcedureManager ( $dbConnector, $ftpDataSource );
	}

	public function saveDataToBeImported() {
		foreach ( $this->_importDataSourceManager->getSource () as $label => $externalDataSource ) {
			$externalDataSource->saveDataToBeImported ();
		}
	}

	public function getSavedDataToBeImported($owner, $catList, $from = null, $subCategory = null) {
		$toBeImported = [ ];
		foreach ( $this->_importDataSourceManager->getSource () as $label => $externalDataSource ) {
			if (! is_null ( $from ) && $label != $from)
				continue;
			$toBeImported [$label] = $externalDataSource->getSavedDataToBeImported ( $owner, $catList, $subCategory );
		}
		return is_null ( $from ) ? $toBeImported : $toBeImported [$from];
	}

	// Da un file importato ricreo un array di coppie chiave-valore
	// in base agli inputs del mio Master Document
	public function fromFileToPostMetadata($filename, $inputs) {
		$obj_data = unserialize ( file_get_contents ( $filename ) );
		$md_data = [];
		foreach ( $inputs as $input ) :
			$key = ( string ) $input [XMLParser::KEY];
			$value = isset ( $obj_data [$key] ) ? $obj_data [$key] : null;
			
			if (isset ( $input [XMLParser::TRANSFORM] )) {
				$callback = ( string ) $input [XMLParser::TRANSFORM];
				$value = ImportHelper::$callback ( $value );
			}
			
			$md_data [Common::fieldFromLabel(( string ) $input)] = $value;
		endforeach;
		return $md_data;
	}

	public function import($from, $data) {
		$this->clean();
		$externalDataSource = $this->_importDataSourceManager->getSource ( $from );
		
		if (! $externalDataSource)
			return new ErrorHandler ( "$from non registrato come valida sorgente di dati" );

		
			// Rinomino il file per mettergli un lock non fisico
		$import_filename = REAL_ROOT . $data [self::LABEL_IMPORT_FILENAME];
		$import_filename_field = rtrim ( $import_filename, "." . $externalDataSource::FILE_EXTENSION_TO_BE_IMPORTED );
		
		if (! $this->_lock ( $import_filename ))
			return new ErrorHandler ( 'Permessi di scrittura negati sul server' );
			
		//unset ( $data [self::LABEL_IMPORT_FILENAME] );
		
		// Tramite un'unica transazione vado a scrivere i dati nelle tabelle
		// master_document e master_document_data.
		// Al primo errore riscontrato faccio ROLLBACK e segnalo l'errore
		
		$XMLParser = new XMLParser ( $data [self::LABEL_MD_XML], $data [self::LABEL_MD_TYPE] );
		
		$md = $this->_generateMDRecord ( $data );
		$md_data = $XMLParser->generateData ( 
				$this->fromFileToPostMetadata ( $import_filename, $XMLParser->getMasterDocumentInputs () ), 
				$XMLParser->getMasterDocumentInputs () );
		
		// Se nel file importato mancano dei valori di inputs obbligatori mi fermo subito
		if(!$md_data){
			$this->_unlock ( $import_filename );
			return new ErrorHandler ( "Importazione fallita, mancano informazioni obbligatore" );
		}
		
		// Inizio la transazione
		$this->_dbConnector->begin ();
		$md = $this->_ProcedureManager->createMasterdocument ( $md, $md_data );
		// Creo il Master Document
		if (! $md ) {
			$this->_unlock ( $import_filename );
			$this->_dbConnector->rollback ();
			return new ErrorHandler ( "Creazione Master Document fallita, impossibile continuare" );
		}

		$firstDoc = $XMLParser->getDocList ()[0];
		$XMLParser->checkIfMustBeLoaded($firstDoc);
		
		$doc = $this->_generateDocRecord ( ( string )$firstDoc [XMLParser::DOC_NAME], $md [Masterdocument::ID_MD], "pdf", $import_filename_field );
		
		// Il pdf per ora lo prendo da un fakefile...
		$filePath = /*REAL_ROOT . self::FAKEFILE;*/
			$externalDataSource->getExternalDocument(FILES_PATH, unserialize ( file_get_contents ( $import_filename ) ));
		
		$mdfolder=$md [Masterdocument::FTP_FOLDER] 
		. $this->_ftpDataSource->getFolderNameFromMasterdocument($md) 
		. DIRECTORY_SEPARATOR;
		
		// Creo il documento
		
		if (! $this->_ProcedureManager->createDocument ( $doc, null, $filePath, $mdfolder )) {
			$this->_unlock ( $import_filename );
			$this->_dbConnector->rollback ();
			$this->_ProcedureManager->removeMasterdocumentFolder ( $mdfolder );
			return new ErrorHandler ( "Creazione Document fallita, impossibile continuare" );
		}
		
		// Se arrivo fin qui è andato tutto a buon fine
		$this->_setImported ( $import_filename, $externalDataSource::FILE_EXTENSION_TO_BE_IMPORTED, $externalDataSource::FILE_EXTENSION_IMPORTED );
		$this->_dbConnector->commit ();
		return new ErrorHandler ( false );
	}

	public function createDataFromSaved(array $record) {
		$XMLParser = new XMLParser ( $lastXML [XMLDataSource::LABEL_XML], $record [self::LABEL_MD_TYPE] );
	}

	public function clean() {
		// Sblocco tutti gli import non andati a buon fine e che mi hanno
		// lasciato
		// I file rinominati con l'estensione del mio utente
		foreach ( $this->_importDataSourceManager->getSource () as $label => $externalDataSource ) {
			$list = glob ( REAL_ROOT . $externalDataSource::IMPORT_PATH . "*" );
			if (! empty ( $list )) {
				foreach ( $list as $folder ) {
					$files = glob ( $folder . "/*" . $this->_getLockPattern () );
					foreach ( $files as $file ) {
						$this->_unlock ( $file );
					}
				}
			}
		}
	}

	private function _getLockPattern() {
		return "." . Session::getInstance ()->get ( AUTH_USER );
	}

	private function _lock(&$filename) {
		$oldname = $filename;
		$filename = $oldname . $this->_getLockPattern ();
		return @rename ( $oldname, $filename );
	}

	private function _unlock(&$filename) {
		$oldname = $filename;
		$filename = str_replace ( $this->_getLockPattern (), "", $oldname );
		return @rename ( $oldname, $filename );
	}

	private function _setImported(&$filename, $toBeImportedExtension, $importedExtension) {
		$this->_unlock ( $filename );
		$oldname = $filename;
		$filename = str_replace ( "." . $toBeImportedExtension, "." . $importedExtension, $oldname );
		return @rename ( $oldname, $filename );
	}

	private function _generateMDRecord($data) {
		return array (
				Masterdocument::NOME => $data [self::LABEL_MD_NOME],
				Masterdocument::TYPE => $data [self::LABEL_MD_TYPE],
				Masterdocument::XML => $data [self::LABEL_MD_XML] 
		);
	}

	private function _generateDocRecord($nome, $id_md, $extension, $importfilename) {
		return array (
				Document::NOME => $nome,
				Document::ID_MD => $id_md,
				Document::EXTENSION => $extension,
				Document::IMPORTED_FILE_NAME => $importfilename 
		);
	}
}
?>