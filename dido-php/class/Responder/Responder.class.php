<?php 
class Responder{
	private $_XMLBrowser, $_ownerRules;
	private $_Masterdocument, $_MasterdocumentData;
	private $_md, $_md_data;
	private $_documents, $_documents_data;
	private $_alreadyCreated = false;
	
	public function __construct(){
		$this->_XMLBrowser = XMLBrowser::getInstance();
		$this->_XMLBrowser->filterXmlByServices(PermissionHelper::getInstance()->getUserField('gruppi'));
		$this->_Masterdocument = new Masterdocument(Connector::getInstance());
		$this->_MasterdocumentData = new MasterdocumentData(Connector::getInstance());
		
		$this->_ownerRules = (array)simplexml_load_file(FILES_PATH."ownerRules.xml")->inputField;
	}
	
	public function createDocList($notClosed = false){
		$this->_alreadyCreated = true;
		
		if(!PermissionHelper::getInstance()->isConsultatore()){
			// Utente Normale, può vedere solo i documenti di cui è proprietario o firmatario e basta
			$key_value = array();
			foreach($this->_ownerRules as $inputField){
				$key_value[$inputField] = PermissionHelper::getInstance()->getUserId();
			}
			$md_data = $this->_Masterdocument->searchByKeyValue($key_value);
			$ids_md = Utils::getListfromField($md_data, "id_md");
			$this->_md = $this->_Masterdocument->getBy("id_md",join(",", array_values($ids_md)));
		} else {
			$xmlList = array_keys($this->_XMLBrowser->getXmlList(false));
			$xmlList = array_map("Utils::apici",$xmlList);
			// Altrimenti filtro in base agli XML di cui ho visione secondo il mio ruolo
			$this->_md = $this->_Masterdocument->getBy("xml",join(",", $xmlList));
		}
		if($notClosed) 
			$this->_md = Utils::filterList($this->_md, "closed", 0);
		
		$this->_md = Utils::getListfromField($this->_md,null,"id_md");
		
		$md_ids = array_keys($this->_md);
		$this->_md_data = $this->_compact(Utils::groupListBy($this->_MasterdocumentData->getBy("id_md", join(",",$md_ids)), "id_md"));

		$Document = new Document(Connector::getInstance());
		$this->_documents = Utils::getListfromField($Document->getBy("id_md", join(",",$md_ids)), null, "id_doc");
		
		$DocumentData = new DocumentData(Connector::getInstance());
		$this->_documents_data = $this->_compact(Utils::groupListBy($DocumentData->getBy("id_doc", join(",",array_keys($this->_documents))),"id_doc"));
		
	}
	
	public function getMyMasterDocuments(){
		if(!$this->_alreadyCreated) 
			$this->createDocList();
		
		return array(
			'md' => $this->_md,
			'md_data' => $this->_md_data,
			'documents' => $_documents,
			'documents_data' => $_documents_data
		);
	}
	
	private function _compact($md_data){
		foreach($md_data as $id_md=>$data){
			$metadata = array();
			foreach($data as $input){
				$metadata[$input['key']] = $input['value'];
			}
			$md_data[$id_md] = $metadata;
		}
		return $md_data;
	}
}