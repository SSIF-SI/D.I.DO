<?php 
class Importer{
	private $_FTPConnector;
	private $_connInstance;
	private $_result, $_oldname, $_newname;
	
	public function __construct($FTPConnector = null, $connInstance = null){
		if(is_null($FTPConnector)) $FTPConnector = FTPConnector::getInstance();
		if(is_null($connInstance)) $connInstance = Connector::getInstance();
		$this->_FTPConnector = $FTPConnector;
		$this->_connInstance = $connInstance;
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
					$newname = str_replace(".".Session::getInstance()->get('AUTH_USER'),"",$file);
					@rename($oldname, $newname);
				}
			}
		}
	}
	
	public function import($data){
		// Controllo parametri essenziali
		if( !isset($data['import_filename']) ||
			!isset($data['md_nome'])  ||	
			!isset($data['md_type'])  ||
			!isset($data['md_xml'])) 
				die(json_encode(array('errors' => 'Mancano argomenti')));
		
		Utils::printr($data);
		die();
		
		// Se non trovo il file vuol dire che è stato importato correttamente da qualcun'altro
		if(!file_exists(GECO_IMPORT_PATH.$data['import_filename']))
			die(json_encode(array('errors' => false)));
		
		// Rinomino il file per mettergli un lock non fisico
		$this->_oldname = GECO_IMPORT_PATH.$data['import_filename'];
		$this->_newname = GECO_IMPORT_PATH.$data['import_filename'].".".Session::getInstance()->get('AUTH_USER');
		$rename = rename($this->_oldname, $this->_newname);
		if(!$rename)
			die(json_encode(array('errors' => 'Permessi di scrittura negati')));
		
		unset($data['import_filename']);
				
		// Tramite un'unica transazione vado a scrivere i dati nelle tabelle 
		// master_document e master_document_data.
		// Al primo errore riscontrato faccio ROLLBACK e segnalo l'errore
		
		$this->_connInstance->query("BEGIN");
		
		$ftp_folder = dirname($data['md_xml']).DIRECTORY_SEPARATOR.date("Y").DIRECTORY_SEPARATOR.date("m");
		
		$md = array (
			'nome' 			=> $data['md_nome'],
			'type' 			=> $data['md_type'],
			'xml'  			=> $data['md_xml'],
			'ftp_folder'	=> $ftp_folder,
			'closed'		=> 0				
		);
		
		$Masterdocument = new Masterdocument($this->_connInstance);
		$this->_result = $Masterdocument->save($md);
		$this->_checkErrors();

		unset($data['md_nome'], $data['md_type'], $data['md_xml']);
		$id_md = $this->_connInstance->getLastInsertId();
		$masterdocumentData = new MasterdocumentData($this->_connInstance);
		
		foreach($data as $key=>$value){
			$key = FormHelper::labelFromField($key);
			$md_data = array(
				'id_md'	=> $id_md,
				'key'	=> $key,
				'value' => $value
			);
			
			$this->_result = $masterdocumentData->save($md_data);
			$this->_checkErrors();
		}	
		
		// Creo la cartella ftp
		if (!$this->_FTPConnector->ftp_mksubdirs($ftp_folder . DIRECTORY_SEPARATOR . $md['nome'] . "_" . $id_md)){
			$this->_result['errors'] = "Cannot create subdirs in FTP";
			$this->_checkErrors();
		}
		
		// TODO: Importo/Creo il documento pdf
		
		// Se tutto ok sposto il file nella cartella imported e faccio COMMIT
		if(!file_exists(GECO_IMPORT_PATH.dirname($this->_newname)."/import"))
			mkdir(GECO_IMPORT_PATH.dirname($this->_newname)."/import");
		rename($this->_newname, GECO_IMPORT_PATH.dirname($this->_newname)."/import/".basename($this->_newname));
		
		$this->_connInstance->query("COMMIT");
		die(json_encode(array('errors' => false)));
		
	}
	
	private function _checkErrors(){
		if(!empty($this->_result['errors'])){
			$this->_connInstance->query("ROLLBACK");
			rename($this->_newname, $this->_oldname);
			die(json_encode(array('errors' => $this->_result['errors'])));
		}
	}
}

?>