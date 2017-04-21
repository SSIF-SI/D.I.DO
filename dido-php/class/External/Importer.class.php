<?php 
class Importer{
	private $_FTPConnector;
	private $_connInstance;
	private $_result, $_oldname, $_newname;
	
	const IMPORTED = "imported";
	const FAKEFILE = "fakefile/sample06.pdf";
	
	
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
	
	public function import($data){
		// Controllo parametri essenziali
		if( !isset($data['import_filename']) ||
			!isset($data['md_nome'])  ||	
			!isset($data['md_type'])  ||
			!isset($data['md_xml'])) 
				die(json_encode(array('errors' => 'Mancano argomenti')));
		
		// Se non trovo il file vuol dire che è stato importato correttamente da qualcun'altro
		if(!file_exists(GECO_IMPORT_PATH.$data['import_filename']))
			die(json_encode(array('errors' => "Il file è già stato importato")));
		
		// Rinomino il file per mettergli un lock non fisico
		$this->_oldname = GECO_IMPORT_PATH.$data['import_filename'];
		$this->_newname = $this->_lock(GECO_IMPORT_PATH.$data['import_filename']);
		$rename = rename($this->_oldname, $this->_newname);
		if(!$rename)
			die(json_encode(array('errors' => 'Permessi di scrittura negati')));
		$importfilename=$data['import_filename'];
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

		$xml = XMLBrowser::getInstance()->getSingleXml($data['md_xml']);
		XMLParser::getInstance()->setXMLSource($xml, $data['md_type']);
		$inputs = XMLParser::getInstance()->getMasterDocumentInputs();
		
		// Recupero il primo documento dell'xml
		$firstDoc = null;
		
		foreach(XMLParser::getInstance()->getDocList() as $document){
			if(!is_null($document['load'])){
				$defaultXml = XML_STD_PATH . (string)$document['load'];
				$document = simplexml_load_file($defaultXml);
			}	
			$firstDoc = $document;
			break;			
		}
			
		
		unset($data['md_nome'], $data['md_type'], $data['md_xml']);
		$id_md = $this->_connInstance->getLastInsertId();
		$masterdocumentData = new MasterdocumentData($this->_connInstance);
		
		foreach($inputs as $input){
			$data_key = FormHelper::fieldFromLabel((string)$input);
			$mandatory = isset($input['mandatory']) ? (bool)(string)$input['mandatory'] : true;
			//var_dump($mandatory);
			
			if(empty($data[$data_key])){
				if ($mandatory){
					//Utils::printr("Not found $data_key, but MANDATORY");
					$this->_result['errors'] = "Manca il valore di \"$input\", impossibile continuare.";
					break;
				} else {
					//Utils::printr("Not found $data_key, SKIPPING");
					continue;
				}
			}
			
			//Utils::printr("found $data_key, converting to $input");
				
			$value = $data[$data_key];
			unset($data[$data_key],$input['mandatory']);
			$data[(string)$input] = $value;
		}	
		
		//Utils::printr($data);
		//die();
		
		$this->_checkErrors();
		
		$this->_result = $masterdocumentData->saveInfo($data, $id_md, true);
		$this->_checkErrors();
		
		$ftpPath=$ftp_folder . DIRECTORY_SEPARATOR.$md['nome'] . "_" . $id_md;
		// Creo la cartella ftp
		if (!$this->_FTPConnector->ftp_mksubdirs($ftpPath)){
			$this->_result['errors'] = "Cannot create subdirs in FTP";
			$this->_checkErrors();
		}
		
		// TODO: Importo/Creo il documento pdf
		
		$this->addDocument($firstDoc['name'],$id_md,"pdf",$importfilename);
		
		$fakefile=FormHelper::fieldFromLabel($firstDoc['name']." ".$this->_result['lastInsertId'].".pdf");
		$this->_importFakePdf($ftpPath,$fakefile);
	
		//if($this->_FTPConnector->file_exists($ftpPath.DIRECTORY_SEPARATOR.$fakefile)){
		//	$this->addDocument($firstDoc['nome'],$id_md,"pdf",$importfilename);
		//}
		
		if(!file_exists(dirname($this->_newname).DIRECTORY_SEPARATOR.self::IMPORTED)){
			mkdir(dirname($this->_newname).DIRECTORY_SEPARATOR.self::IMPORTED);
		}
		// Se tutto ok sposto il file nella cartella imported e faccio COMMIT
		rename($this->_newname, dirname($this->_newname).DIRECTORY_SEPARATOR.self::IMPORTED.DIRECTORY_SEPARATOR. basename($this->_unlock($this->_newname)));
		

		
		$this->_connInstance->query("COMMIT");
		
		
		die(json_encode(array('errors' => false)));
		
	}
	// Rinomino e Copio il file sample06.pdf nella directory FTP dove dovrà essere trasferito il file importato  
	private function _importFakePdf($ftpPath, $fakefile){
		copy(REAL_ROOT.self::FAKEFILE ,REAL_ROOT."fakefile".DIRECTORY_SEPARATOR.$fakefile);
		$this->_FTPConnector->upload(REAL_ROOT."fakefile".DIRECTORY_SEPARATOR.$fakefile, $ftpPath.DIRECTORY_SEPARATOR.$fakefile);
		
	}
	
	private function addDocument($nome,$id_md,$extension,$importfilename){
		$doc = array (
				'nome' 			=> $nome,
				'id_md' 			=> $id_md,
				'extension'  			=> $extension,
				'imported_file_name'	=> $importfilename,
				'closed'		=> 0
		);
		$document = new Document($this->_connInstance);
		$this->_result = $document->save($doc);
		$this->_checkErrors();
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