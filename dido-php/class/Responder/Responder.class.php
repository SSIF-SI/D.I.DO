<?php 
class Responder{
	private $_firstCreated = false;
	private $_md, $_md_open, $_md_data;
	private $_documents, $_documents_data;
	
	public function __construct(){
		
		$XMLBrowser = XMLBrowser::getInstance();
		$XMLBrowser->filterXmlByServices(PermissionHelper::getInstance()->getUserField('gruppi'));
		
		$ownerRules = array("dipendente","destinatario", "titolare dei fondi", "responsabile di laboratorio");
		
		$MasterDocument = new Masterdocument(Connector::getInstance());
		
		if(!PermissionHelper::getInstance()->isConsultatore() && !PermissionHelper::getInstance()->isSigner()){
			// Utente Normale, può vedere solo i documenti di cui è proprietario o firmatario e basta 
			$key_value = array();
			foreach($ownerRules as $key){
				$key_value[$key] = PermissionHelper::getInstance()->getUserId();
			}
			$MasterDocumentData = new MasterdocumentData(Connector::getInstance());
			$md_data = $MasterDocumentData->searchByKeyValue($key_value);
			$ids_md = Utils::getListfromField($md_data, "id_md");
			$this->_md = $MasterDocument->getBy("id_md",join(",", array_values($ids_md)));
		} else {
			// Altrimewnti filtro in base agli XML di cui ho visione secondo il mio ruolo
			$this->_md = $MasterDocument->getBy("xml",join(",", XMLBrowser::getInstance()->getXmlList(false)));
		}
	}
	
	public function createDocList($notClosed = false){
		$this->_firstCreated = true;
		$md_ids = array_keys($notClosed ? Utils::filterList($this->_md, "closed", 0) : $this->_md);
		
		$MasterDocumentData = new MasterdocumentData(Connector::getInstance());
		$this->_md_data = Utils::groupListBy($MasterDocumentData->getBy("id_md", join(",",$md_ids)), "id_md");
		
		$Document = new Document(Connector::getInstance());
		$this->_documents = Utils::getListfromField($Document->getBy("id_md", join(",",$md_ids)), null, "id_doc");
		
		$DocumentData = new DocumentData(Connector::getInstance());
		$this->_documents_data = Utils::groupListBy($DocumentData->getBy("id_doc", join(",",array_keys($_documents))),"id_doc");
	}
	
	public function getMyMasterDocuments(){
		if(!$this->_firstCreated)
			$this->createDocList();
		
		return array(
			'md' => $this->_md,
			'md_data' => $this->_md_data,
			'documents' => $_documents,
			'documents_data' => $_documents_data
		);
	}
}