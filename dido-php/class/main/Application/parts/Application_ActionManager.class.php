<?php 
class Application_ActionManager {
	private $_ftpDataSource;
	private $_dbConnector;
	private $_XMLDataSource;
	
	private $_Application_DocumentBrowser;
	private $_Application_Detail;
	private $_ProcedureManager;
	
	const ACTION_LABEL = "action";
	
	const ACTION_UPLOAD = "upload";
	const ACTION_DOWNLOAD = "download";
	const ACTION_EDIT_INFO = "editInfo";
	const ACTION_CLOSE_DOC = "closedocument";
	const ACTION_EDIT_MD_INFO = "editMdInfo";
	
	public function __construct(Application_DocumentBrowser $App_DB, Application_Detail $App_Detail, IDBConnector $dbConnector, IFTPDataSource $ftpDataSource, IXMLDataSource $XMLDataSource){
		$this->_Application_Detail = $App_Detail;
		$this->_Application_DocumentBrowser = $App_DB;
		$this->_dbConnector = $dbConnector;
		$this->_ftpDataSource = $ftpDataSource;	
		$this->_XMLDataSource = $XMLDataSource;
		
		$this->_ProcedureManager = new ProcedureManager($dbConnector, $ftpDataSource);	
	}
	
	public function closedocument(){
		if (!isset($_GET[Document::ID_DOC]))
			return new ErrorHandler("Parametri mancanti");	
		extract($this->_getMd($_GET));
		
		$doc = new Document($this->_dbConnector);
		$doc=Utils::stubFill($doc->getStub(),$documents[$_GET[Document::ID_DOC]]);
		$ARP=new AjaxResultParser();
		$ARP->encode($this->_ProcedureManager->closeDocument($doc));
		
	}
	
	public function download(){
		$md = $this->_getMd($_GET);
		
		if($md instanceof ErrorHandler)
			return $md;
		
		extract($md);
		
		$filename =
			$md[Masterdocument::FTP_FOLDER] .
			Common::getFolderNameFromMasterdocument($md) .
			DIRECTORY_SEPARATOR .
			Common::getFilenameFromDocument($documents[$_GET[Document::ID_DOC]]);
				
		$this->_ftpDataSource->download($filename);
	}
	
	public function editInfo(){
		if (!isset($_GET[Document::ID_DOC]))
			return new ErrorHandler("Parametri mancanti");	
		
		$md = $this->_getMd($_GET);
		
		if($md instanceof ErrorHandler)
			return $md;
		
		extract($md);
		
		$id_doc = $_GET[Document::ID_DOC];		
		$docName = $documents[$id_doc][Document::NOME];

		$XMLParser = new XMLParser();
			
		// TODO: Ricodificare questa bruttura!!!
		if($docName != "allegato"){
			$XMLParser->setXMLSource($md[Masterdocument::XML], $md[Masterdocument::TYPE]);
			$docInputs = $XMLParser->getDocumentInputs($docName);
		} else {
			$XMLParser->load(XML_STD_PATH."allegato.xml");
			$docToSearch = $XMLParser->getXmlSource();
			$docInputs = $docToSearch->inputs->input;
		}
		
		if(count($_POST)){
			$ARP = new AjaxResultParser();
			$ARP->encode(
					$this->_Application_Detail->updateDocumentData(
							$id_doc,
							$docInputs,
							$_POST)
					->getErrors(true));
		}
		
		$docInfo = $this->_Application_Detail->createDocumentInfoPanel($docInputs, $documents_data[$id_doc], false);
		echo("<form>$docInfo</form>");
		Utils::includeScript(SCRIPTS_PATH, "datepicker.js");
		die();
	}
	
	public function upload(){
		$md = $this->_getMd($_GET);
		
		if($md instanceof ErrorHandler)
			return $md;
		
		$ARP = new AjaxResultParser();
		$eh = new ErrorHandler(false);
			
		extract($md);
			
		if (!isset($_GET[Document::ID_DOC])){
			// Nuovo documento, ho bisogno anche del pannello di informazioni
			if(!isset($_GET[XMLParser::DOC_NAME])){
				$eh = new ErrorHandler("Parametri mancanti");
				$ARP->encode($eh->getErrors(true));
			}

			$docName = $_GET[XMLParser::DOC_NAME];
			
			// TODO: Ricodificare questa bruttura!!!
			$XMLParser = new XMLParser();
			
			if($docName != "allegato"){
				$XMLParser->setXMLSource($md[Masterdocument::XML], $md[Masterdocument::TYPE]);
				$docInputs = $XMLParser->getDocumentInputs($docName);
			} else {
				$XMLParser->load(XML_STD_PATH."allegato.xml");
				$docToSearch = $XMLParser->getXmlSource();
				$docInputs = $docToSearch->inputs->input;
			}
			
			if(count($_POST)){
				//Sto salvando qualcosa
				$extension = Common::getFileExtension($_POST['upFileName']);
				$filePath = FILES_PATH . $_POST['upFilePath'];
				
				$repositoryPath = 
					$md [Masterdocument::FTP_FOLDER] 
					. Common::getFolderNameFromMasterdocument($md) 
					. DIRECTORY_SEPARATOR;
				
				unset($_POST['upFileName'], $_POST['upFilePath']);
				
				$doc = new Document($this->_dbConnector);
				
				$closed = 
					$XMLParser->getDocumentSignatures($docName) ?
					ProcedureManager::OPEN :
					ProcedureManager::CLOSED;
					
				$doc = Utils::stubFill($doc->getStub(), [
					Document::ID_MD => $md[Masterdocument::ID_MD],
					Document::NOME	=> $docName,
					Document::EXTENSION => $extension,
					Document::CLOSED => $closed
				]);
				
				$data = Common::createPostMetadata($_POST);
				
				$result = $this->_ProcedureManager->createDocument($doc, $data, $filePath, $repositoryPath);
				
				if(!$result){
					$eh->setErrors("Impossibile creare il documento");
				}
				
				$ARP->encode($eh->getErrors(true));
			}
			
			$docInfo = $this->_Application_Detail->createDocumentInfoPanel($docInputs, $documents_data[$id_doc], false);
		}
		include(VIEWS_PATH."docUpload.php");
	}
	
	public function editMdInfo(){
		if(count($_POST)){
			$ARP = new AjaxResultParser();
			$eh = new ErrorHandler(false);

			if(isset($_GET[Masterdocument::ID_MD])){
				$id_md = $_GET[Masterdocument::ID_MD];
				$md_data[$id_md] = Common::createPostMetadata($_POST);
				$result = $this->_ProcedureManager->updateMasterdocument($md_data);
				
				if(!$result)
					$eh->setErrors("Impossibile aggiornare i dati");
			} else {
			
				$md_name = $_GET[XMLParser::MD_NAME];
					
				$filters = [$md_name];
				
				$xml = $this->_XMLDataSource
					->filter(new XMLFilterDocumentType($filters))
					->filter(new XMLFilterValidity(date("Y-m-d")))
					->getFirst();
				
				$type = isset($_POST[XMLParser::TYPE]) ? $_POST[XMLParser::TYPE] : null;
				unset($_POST[XMLParser::TYPE]);
				
				$md = [
					Masterdocument::NOME 	=> $md_name,
					Masterdocument::TYPE 	=> $type,
					Masterdocument::XML 	=> $xml[XMLDataSource::LABEL_FILE]
				];
				
				$md_data = Common::createPostMetadata($_POST);
				
				$md = $this->_ProcedureManager->createMasterdocument($md, $md_data);
					
				if(!$md)
					$eh->setErrors("Impossibile salvare i dati");
				else {
					$eh->setOtherData("href", BUSINESS_HTTP_PATH."document.php?".Masterdocument::ID_MD."=".$md[Masterdocument::ID_MD]);
				}
			}
			
			$ARP->encode($eh->getErrors(true));
		}
		
		$XMLParser = new XMLParser();
		$md_data = [];
		
		if(isset($_GET[Masterdocument::ID_MD])){
			$md = $this->_getMd($_GET);
			
			if($md instanceof ErrorHandler)
				return $md;
			
			extract($md);
			$XMLParser->setXMLSource($md[Masterdocument::XML], $md[Masterdocument::TYPE]);
		} else {
			// New
			$md_name = $_GET[XMLParser::MD_NAME];
				
			$filters = [$md_name];
				
			$xml = $this->_XMLDataSource
			->filter(new XMLFilterDocumentType($filters))
			->filter(new XMLFilterValidity(date("Y-m-d")))
			->getFirst();

			$XMLParser->setXMLSource($xml[XMLDataSource::LABEL_XML]);
			
			$types = $XMLParser->getDocTypes();
			
		}
		
		$docInfo = $this->_Application_Detail->createDocumentInfoPanel($XMLParser->getMasterDocumentInputs(), $md_data, false, true);
		if(!empty($types)){
			$types = array_combine($types, array_map(function($el){return ucfirst($el);}, $types));
			$docInfo = HTMLHelper::select(XMLParser::TYPE, "Tipo", $types, null, null, false, true).$docInfo;
		}
		
		echo("<form>$docInfo</form>");
		Utils::includeScript(SCRIPTS_PATH, "datepicker.js");
		die();		
	}
	
	private function _getMD(){
		if(!isset($_GET[Masterdocument::ID_MD]))
			return new ErrorHandler("Parametri mancanti");
		
		$id_md = $_GET[Masterdocument::ID_MD];
		$md = $this->_Application_DocumentBrowser->get($id_md);
		
		if(!$md)
			return new ErrorHandler("Master Document non trovato");
			
		return $md;
	}
	
}
?>