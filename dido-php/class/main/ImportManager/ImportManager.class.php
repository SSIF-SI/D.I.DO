<?php
class ImportManager{
	private $_importDataSourceManager;
	private $_oldname, $_newname;
	
	public function __construct(){
		$this->_importDataSourceManager = new ImportDataSourceManager();
	}
	
	public function saveDataToBeImported(){
		foreach($this->_importDataSourceManager->getSource() as $label => $externalDataSource){
			$dataToBeImported = array_merge($dataToBeImported, $externalDataSource->saveDataToBeImported());
		}
	}
	
	public function getSavedDataToBeImported($owner, $catList, $from = null){
		$toBeImported = [];
		foreach($this->_importDataSourceManager->getSource() as $label => $externalDataSource){
			if(!is_null($from) && $label != $from) 
				continue;
			$toBeImported[$label] = $externalDataSource->getSavedDataToBeImported($owner, $catList);
		}
		return $toBeImported;
	}

	public function import($from, $data){
		$externalDataSource = $this->_importDataSourceManager->getSource($from);
		
		if(!$externalDataSource)
			return "$from non registrato come valida sorgente di dati";
		
		// Controllo parametri essenziali
		if( !isset($data['import_filename']) 	||
			!isset($data['md_nome'])  			||
			!isset($data['md_type'])  			||
			!isset($data['md_xml']))
			return 'Mancano argomenti essenziali';
	
		// Rinomino il file per mettergli un lock non fisico
		$this->_oldname = $externalDataSource::IMPORT_PATH.$data['import_filename'];
		$this->_newname = $this->_lock(IMPORT_PATH.$data['import_filename']);
		$rename = rename($this->_oldname, $this->_newname);
		if(!$rename)
			return 'Permessi di scrittura negati';
		
		$importfilename=$data['import_filename'];
		unset($data['import_filename']);
		
		// Tramite un'unica transazione vado a scrivere i dati nelle tabelle
		// master_document e master_document_data.
		// Al primo errore riscontrato faccio ROLLBACK e segnalo l'errore
		
	}
	
	public function clean(){
		// Sblocco tutti gli import non andati a buon fine e che mi hanno lasciato
		// I file rinominati con l'estensione del mio utente
		$list = glob(GECO_IMPORT_PATH."*");
		if(!empty($list)){
			foreach($list as $folder){
				$files = glob($folder."/*.".Session::getInstance()->get('AUTH_USER'));
				foreach($files as $file){
					$oldname = $file;
					$newname = $this->_unlock($file);
					@rename($oldname, $newname);
				}
			}
		}
	}
	
	private function _lock($filename){
		return $filename.".".Session::getInstance()->get('AUTH_USER');
	}
	
	private function _unlock($filename){
		return str_replace(".".Session::getInstance()->get('AUTH_USER'),"",$filename);
	}
}
?>