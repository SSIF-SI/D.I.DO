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
	const ACTION_DELETE = "delete";
	const ACTION_DELETE_MD_LINK = "deleteMdLink";
	const ACTION_EDIT_INFO = "editInfo";
	const ACTION_EDIT_MD_LINK = "editMdLink";
	const ACTION_CLOSE_DOC = "closedocument";
	const ACTION_CLOSE_MD = "closemd";
	const ACTION_EDIT_MD_INFO = "editMdInfo";
	const ACTION_SETPRIVATE = "setPrivate";
	
	const LABEL_PRIVATE = "Privato";
	const LABEL_VISIBLE = "Visibile";
	
	
	public function __construct(Application_DocumentBrowser $App_DB, Application_Detail $App_Detail, IDBConnector $dbConnector, IFTPDataSource $ftpDataSource, IXMLDataSource $XMLDataSource){
		$this->_Application_Detail = $App_Detail;
		$this->_Application_DocumentBrowser = $App_DB;
		$this->_dbConnector = $dbConnector;
		$this->_ftpDataSource = $ftpDataSource;	
		$this->_XMLDataSource = $XMLDataSource;
		
		$this->_ProcedureManager = new ProcedureManager($dbConnector, $ftpDataSource);	
	}
	
	public function delete(){
		if (!isset($_GET[Document::ID_DOC]))
			return new ErrorHandler("Parametri mancanti");
		$result = $this->_getMd($_GET);
// 		flog($result);
		extract ($result);
		
		$doc = new Document($this->_dbConnector);
		$doc=Utils::stubFill($doc->getStub(),$documents[$_GET[Document::ID_DOC]]);
		$ftpFolder=$md[Masterdocument::FTP_FOLDER].Common::getFolderNameFromMasterdocument($md).DIRECTORY_SEPARATOR;
		$ARP=new AjaxResultParser();
		$ARP->encode($this->_ProcedureManager->deleteDocument($doc, $ftpFolder));
	}
	
	public function closedocument(){
		if (!isset($_GET[Document::ID_DOC]))
			return new ErrorHandler("Parametri mancanti");	
		$result = $this->_getMd($_GET);
		extract ($result);
		
		$doc = new Document($this->_dbConnector);
		$doc=Utils::stubFill($doc->getStub(),$documents[$_GET[Document::ID_DOC]]);
		$ARP=new AjaxResultParser();
		$ARP->encode($this->_ProcedureManager->closeDocument($doc));
		
	}
	
	public function closemd(){
		if (!isset($_GET[Masterdocument::ID_MD]))
			return new ErrorHandler("Parametri mancanti");	
		$result = $this->_getMd($_GET);
		extract ($result);
		
		$status = isset($_GET[Masterdocument::CLOSED]) ? $_GET[Masterdocument::CLOSED] : ProcedureManager::CLOSED;
		$ARP=new AjaxResultParser();
		$ARP->encode($this->_ProcedureManager->closeMasterdocument($md, $status));
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
		
		$innerValues = $XMLParser->getMasterDocumentInnerValues();
		
		if(count($_POST)){
			$ARP = new AjaxResultParser();
			$ARP->encode(
					$this->_Application_Detail->updateDocumentData(
							$id_doc,
							$docInputs,
							$_POST)
					->getErrors(true));
		}
		
		$docInfo = $this->_Application_Detail->createDocumentInfoPanel($docInputs, $documents_data[$id_doc], $innerValues, false);
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
				$docTypes = $XMLParser->getDocTypes();
			} else {
				$XMLParser->load(XML_STD_PATH."allegato.xml");
				$docToSearch = $XMLParser->getXmlSource();
				$docInputs = $docToSearch->inputs->input;
			}
			
			$innerValues = $XMLParser->getMasterDocumentInnerValues();
			
			$docInfo = $this->_Application_Detail->createDocumentInfoPanel($docInputs, $documents_data[$id_doc], $innerValues, false);
			if($docTypes){
				$options = array();
				foreach($docTypes as $type){
					if((string)$type[XMLParser::TYPE_FOR]==$docName)
						$options[(string)$type] = (string) $type;
				}
				$docInfo = HTMLHelper::select(Document::TYPE, "Tipo di $docName", $options). $docInfo;
			}
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
		
			$docType = $_POST[XMLParser::TYPE];
				
			$doc = new Document($this->_dbConnector);
			if (!isset($_GET[Document::ID_DOC])){
				$signatures = $XMLParser->getDocumentSignatures($docName, $docType);
				$specialSignatures = $XMLParser->getDocumentSpecialSignatures($docName, $docType);
				
				// Il documento viene chiuso in automatico se non ci sono firme digitali previste
				$closed =
					SignatureChecker::emptySignatures($signatures->signature) && SignatureChecker::emptySignatures($specialSignatures->specialSignature) ?
					ProcedureManager::CLOSED :
					ProcedureManager::OPEN;
				$doc = Utils::stubFill($doc->getStub(), [
						Document::ID_MD => $md[Masterdocument::ID_MD],
						Document::NOME	=> $docName,
						Document::TYPE	=> $_POST[Document::TYPE],
						Document::EXTENSION => $extension,
						Document::CLOSED => $closed,
						Document::PRIVATE_DOC => ProcedureManager::VISIBLE
				]);
			
				unset($_POST[Document::TYPE]);
				
				$data = Common::createPostMetadata($_POST);
			
				$result = $this->_ProcedureManager->createDocument($doc, $data, $filePath, $repositoryPath);
			} else {
				$doc = Utils::stubFill($doc->getStub(),$documents[$_GET[Document::ID_DOC]]);
				$oldExtension=$doc[Document::EXTENSION]==$extension?null:$doc[Document::EXTENSION];
				$doc[Document::EXTENSION]=$extension;
				$result = $this->_ProcedureManager->updateDocument($doc, null, $filePath, $repositoryPath,$oldExtension);
			}
			if(!$result){
				$eh->setErrors("Impossibile creare il documento");
			} else {
				Session::getInstance()->set(SignatureDispatcher::OVERWRITE_FILE_SIGNED, true);
			}
			
			$ARP->encode($eh->getErrors(true));
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
			
			$types = (array) $XMLParser->getMdDocTypes();
			
		}
		$docInfo = $this->_Application_Detail->createDocumentInfoPanel($XMLParser->getMasterDocumentInputs(), $md_data, $XMLParser->getMasterDocumentInnerValues(), false, true);
		if(!empty($types)){
			$types = array_combine($types, array_map(function($el){return ucfirst($el);}, $types));
			$docInfo = HTMLHelper::select(XMLParser::TYPE, "Tipo", $types, null, null, false, true).$docInfo;
		}
		echo("<form>$docInfo</form>");
		Utils::includeScript(SCRIPTS_PATH, "datepicker.js");
				
	}
	
	public function editMdLink(){
		$MasterdocumentsLinks = new MasterdocumentsLinks($this->_dbConnector);
		
		if(isset($_POST['mdLink'])){
			$ARP = new AjaxResultParser();
			
			if(empty($_POST['mdLink'])){
				$eh = new ErrorHandler(false);
				$eh->setErrors("Nessuna scelta effettuata");
			} else {
				$stub = [
						MasterdocumentsLinks::ID_FATHER => $_GET[Masterdocument::ID_MD], 
						MasterdocumentsLinks::ID_CHILD => $_POST['mdLink']
					];
				if(isset($_GET[MasterdocumentsLinks::ID_LINK])){
					$stub[MasterdocumentsLinks::ID_LINK] = $_GET[MasterdocumentsLinks::ID_LINK];
				}
				$eh = $MasterdocumentsLinks->save($stub);
				/*if(!$result)
					$eh->setErrors("Impossibile salvare i dati");*/
			}
			$ARP->encode($eh->getErrors(true));
			die();
		} else {
			$mdLinks = Utils::getListfromField($MasterdocumentsLinks->getBy(MasterdocumentsLinks::ID_FATHER, $_GET[Masterdocument::ID_MD]), MasterdocumentsLinks::ID_CHILD, MasterdocumentsLinks::ID_LINK);
// 			flog("mdLinks Action: %o",$mdLinks);
			$list = $this->_Application_DocumentBrowser->getLinkableMd($_GET[XMLParser::MD_NAME]);
			
			$options = array();
			if(count($list[Application_DocumentBrowser::LABEL_MD])){
				$XMLParser = new XMLParser();
				foreach($list[Application_DocumentBrowser::LABEL_MD] as $id_md=>$md){
					$key = array_search($id_md, $mdLinks);
					
					if(in_array($id_md, $mdLinks) && $key !== false && $key != $_GET[MasterdocumentsLinks::ID_LINK]) continue;
					
					// Teoricamente dovrebbero essere MD chiusi, 
					// In fase di test skippiamo questo controllo 
					// if($md[Masterdocument::CLOSED] != ProcedureManager::CLOSED) continue;
					
					$XMLParser->setXMLSource($md[Masterdocument::XML], $md[Masterdocument::TYPE]);
					$inputs = $XMLParser->getMasterDocumentInputs();
					$optLabel = [];
					foreach($inputs as $input){
						if(isset($input[XMLParser::SHORTWIEW])){
							$key = Common::labelFromField((string)$input, false);
							$value= Common::renderValue($list[Application_DocumentBrowser::LABEL_MD_DATA][$id_md][$key],$input);
							array_push($optLabel, "$key: $value");
						}
					}
					$options[$id_md] = join($optLabel,", ");
				}
			}
// 			flog("options: %o",$options);
			
			$selectedLink = isset($mdLinks[$_GET[MasterdocumentsLinks::ID_LINK]]) ? $mdLinks[$_GET[MasterdocumentsLinks::ID_LINK]] : null;
// 			flog("Selected: %d",$selectedLink);
			die("<form>".HTMLHelper::select("mdLink", $_GET[XMLParser::MD_NAME], $options, $selectedLink)."</form>");
		}
	}
	
	public function deleteMdLink(){
		$MasterdocumentsLinks = new MasterdocumentsLinks($this->_dbConnector);
		$ARP = new AjaxResultParser();
			
		if(empty($_GET[MasterdocumentsLinks::ID_LINK])){
			$eh = new ErrorHandler(false);
			$eh->setErrors("Nessun documento da cancellare");
		} else {
			$record = $MasterdocumentsLinks->get([MasterdocumentsLinks::ID_LINK => $_GET[MasterdocumentsLinks::ID_LINK]]);
			$eh = $MasterdocumentsLinks->delete($record);
		}
		$ARP->encode($eh->getErrors(true));
		die();
	}
	
	public function setPrivate(){
		if (!isset($_GET[Document::ID_DOC]))
			return new ErrorHandler("Parametri mancanti");
		$result = $this->_getMd($_GET);
		extract ($result);
		
		$doc = new Document($this->_dbConnector);
		$doc=Utils::stubFill($doc->getStub(),$documents[$_GET[Document::ID_DOC]]);
		$result=$this->_ProcedureManager->setPrivate($doc);
		if(!$doc[Document::PRIVATE_DOC] && $result){
			$newclass = "btn btn-danger private-doc";
			$newspan="fa fa-eye-slash fa-1x fa-fw";
			$spantext= self::LABEL_PRIVATE;
		}else{
		 	$newclass= "btn btn-success private-doc";
		 	$newspan="fa fa-eye fa-1x fa-fw";
		 	$spantext= self::LABEL_VISIBLE;
		 }
		$ARP=new AjaxResultParser();
		$ARP->encode(["result" => $result, "newclass" => $newclass,"newspan"=>$newspan,"spantext"=>$spantext] );
		
				
	}
	
	private function _getMD($GET){
		if(!isset($GET[Masterdocument::ID_MD]))
			return new ErrorHandler("Parametri mancanti");
		
		$id_md = $GET[Masterdocument::ID_MD];
		$md = $this->_Application_DocumentBrowser->get($id_md);
		
		if(!$md)
			return new ErrorHandler("Master Document non trovato");
			
		return $md;
	}
	
	

}
?>