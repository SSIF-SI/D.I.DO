<?php

class ImportManager {

	const LABEL_IMPORT_FILENAME = "import_filename";

	const LABEL_MD_NOME = "md_nome";

	const LABEL_MD_TYPE = "md_type";

	const LABEL_MD_XML = "md_xml";

	const FAKEFILE = "fakefile/sample06.pdf";

	private $_dbConnector;

	private $_importDataSourceManager;

	private $_ProcedureManager;
	
	public function __construct(IDBConnector $dbConnector, IFTPDataSource $ftpDataSource) {
		$this->_dbConnector = $dbConnector;
		$this->_importDataSourceManager = new ImportDataSourceManager ();
		$this->_ProcedureManager = new ProcedureManager ( $dbConnector, $ftpDataSource );
	}

	public function saveDataToBeImported() {
		foreach ( $this->_importDataSourceManager->getSource () as $label => $externalDataSource ) {
			$dataToBeImported = array_merge ( $dataToBeImported, $externalDataSource->saveDataToBeImported () );
		}
	}

	public function getSavedDataToBeImported($owner, $catList, $from = null) {
		$toBeImported = [ ];
		foreach ( $this->_importDataSourceManager->getSource () as $label => $externalDataSource ) {
			if (! is_null ( $from ) && $label != $from)
				continue;
			$toBeImported [$label] = $externalDataSource->getSavedDataToBeImported ( $owner, $catList );
		}
		return $toBeImported;
	}

	public function import($from, $data) {
		$externalDataSource = $this->_importDataSourceManager->getSource ( $from );
		
		if (! $externalDataSource)
			return new ErrorHandler ( "$from non registrato come valida sorgente di dati" );
			
			// Controllo parametri essenziali
		if (! $this->_checkEssensials ( $data ))
			return new ErrorHandler ( 'Mancano argomenti essenziali' );
			
			// Rinomino il file per mettergli un lock non fisico
		$import_filename = $externalDataSource::IMPORT_PATH . $data [self::LABEL_IMPORT_FILENAME];
		$import_filename_field = rtrim ( $import_filename, "." . $externalDataSource::FILE_EXTENSION_TO_BE_IMPORTED );
		if (! $this->_lock ( $import_filename ))
			return new ErrorHandler ( 'Permessi di scrittura negati sul server' );
		
		unset ( $data [self::LABEL_IMPORT_FILENAME] );
		
		// Tramite un'unica transazione vado a scrivere i dati nelle tabelle
		// master_document e master_document_data.
		// Al primo errore riscontrato faccio ROLLBACK e segnalo l'errore
		
		$XMLParser = new XMLParser ( $data [self::LABEL_MD_XML], $data [self::LABEL_MD_TYPE] );
		
		$md = $this->_generateMDRecord ( $data );
		$md_data = $XMLParser->generateData ( $data, $XMLParser->getMasterDocumentInputs () );
		
		// Inizio la transazione
		$this->_dbConnector->begin ();
		
		// Creo il Master Document
		if (! $this->_ProcedureManager->createMasterdocument ( $md, $md_data )) {
			$this->_unlock ( $import_filename );
			$this->_dbConnector->rollback ();
			return new ErrorHandler ( "Creazione Master Document fallita, impossibile continuare" );
		}
		
		$firstDoc = $XMLParser->getDocList ()[0];
		$doc = $this->_generateDocRecord ( $firstDoc [XMLParser::DOC_NAME], $md [Masterdocument::ID_MD], "pdf", $import_filename_field );
		
		// Il pdf per ora lo prendo da un fakefile...
		$filePath = REAL_ROOT . self::FAKEFILE;
		
		if (! $this->_ProcedureManager->createDocument ( $doc, null, $filePath, $md [Masterdocument::FTP_FOLDER] )) {
			$this->_unlock ( $import_filename );
			$this->_dbConnector->rollback ();
			$this->_ProcedureManager->removeMasterdocumentFolder ( $md [Masterdocument::FTP_FOLDER] );
			return new ErrorHandler ( "Creazione Document fallita, impossibile continuare" );
		}
		
		$this->_setImported ( $import_filename, $externalDataSource::FILE_EXTENSION_TO_BE_IMPORTED, $externalDataSource::FILE_EXTENSION_IMPORTED );
		$this->_dbConnector->commit ();
		return new ErrorHandler ( false );
	}

	public function clean() {
		// Sblocco tutti gli import non andati a buon fine e che mi hanno
		// lasciato
		// I file rinominati con l'estensione del mio utente
		$list = glob ( GECO_IMPORT_PATH . "*" );
		if (! empty ( $list )) {
			foreach ( $list as $folder ) {
				$files = glob ( $folder . "/*". $this->_getLockPattern() );
				foreach ( $files as $file ) {
					$this->_unlock ( $file );
				}
			}
		}
	}

	private function _getLockPattern(){
		return ".". Session::getInstance()->get(AUTH_USER);
	}
	
	private function _lock(&$filename) {
		$oldname = $filename;
		$filename = $oldname . $this->_getLockPattern();
		return @rename ( $oldname, $filename );
	}

	private function _unlock(&$filename) {
		$oldname = $filename;
		$filename = str_replace ( $this->_getLockPattern(), "", $oldname );
		return @rename ( $filename, $newname );
	}

	private function _setImported(&$filename, $toBeImportedExtension, $importedExtension) {
		$this->_unlock ( $filename );
		$oldname = $filename;
		$filename = str_replace ( "." . $toBeImportedExtension, "." . $importedExtension, $oldname );
		return @rename ( $oldname, $filename );
	}

	private function _checkEssentials($data) {
		return ! isset ( $data [self::LABEL_IMPORT_FILENAME] ) || ! isset ( $data [self::LABEL_MD_NOME] ) || ! isset ( $data [self::LABEL_MD_TYPE] ) || ! isset ( $data [self::LABEL_MD_XML] );
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