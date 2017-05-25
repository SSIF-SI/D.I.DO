<?php 
class Application_ActionManager {
	private $_ftpDataSource;
	private $_dbConnector;
	
	private $_Application_DocumentBrowser;
	private $_Application_Detail;
	private $_ProcedureManager;
	
	const ACTION_LABEL = "action";
	
	const ACTION_UPLOAD = "upload";
	const ACTION_DOWNLOAD = "download";
	const ACTION_EDIT_INFO = "editInfo";
	const ACTION_EDIT_MD_INFO = "editMdInfo";
	
	public function __construct(Application_DocumentBrowser $App_DB, Application_Detail $App_Detail, IDBConnector $dbConnector, IFTPDataSource $ftpDataSource){
		$this->_Application_Detail = $App_Detail;
		$this->_Application_DocumentBrowser = $App_DB;
		$this->_dbConnector = $dbConnector;
		$this->_ftpDataSource = $ftpDataSource;	
		
		$this->_ProcedureManager = new ProcedureManager($dbConnector, $ftpDataSource);	
	}
	
	public function download($md){
		extract($md);
		
		$filename =
			$md[Masterdocument::FTP_FOLDER] .
			Common::getFolderNameFromMasterdocument($md) .
			DIRECTORY_SEPARATOR .
			Common::getFilenameFromDocument($documents[$_GET[Document::ID_DOC]]);
				
		$this->_ftpDataSource->download($filename);
	}
	
	public function editInfo($md){
		if (!isset($_GET[Document::ID_DOC]))
			return false;	
		
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
		echo("<form method='POST'>$docInfo</form>");
		Utils::includeScript(SCRIPTS_PATH, "datepicker.js");
		die();
	}
	
	public function upload($md){
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
}
?>