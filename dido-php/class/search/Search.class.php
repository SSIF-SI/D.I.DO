<?php 
class Search{
	private $_tree = [];
	private $_requestUri; 
	private $_view;
	
	const SOURCE 		= "source";
	const SEARCH_URI 	= "Search_URI";
	const ALL 			= "all";
	
	public function __construct(){
		$uriParts = explode("/",$_SERVER['REQUEST_URI']);
		$requestUri = array_pop($uriParts);
		$this->_requestUri = $requestUri;
		$this->_view = $this->parseUri();
	}
	
	public function parseUri(){
		
		if(isset($_GET[self::SOURCE])){
			array_push($this->_tree, $this->_translate($_GET[self::SOURCE]));
		} else return "search_setSource.php";
		
		if(isset($_GET[SharedDocumentConstants::CLOSED]) || isset($_GET[self::ALL])){
			
			array_push($this->_tree, 
				isset($_GET[SharedDocumentConstants::CLOSED]) ? $this->_translate($_GET[SharedDocumentConstants::CLOSED]) : $this->_translate(self::ALL)
			);
			if(!Session::getInstance()->exists(self::SEARCH_URI)) Session::getInstance()->set(self::SEARCH_URI, $this->_requestUri);
			return "search_Panel.php";
		} 
		
		return "search_setClosed.php";
	}
	
	public function getTree(){
		return join(" - ", $this->_tree);
	}
	
	public function getView(){
		return $this->_view;
	}
	
	public function getRequestUri(){
		return $this->_requestUri;
	}
	
	private function _translate($value){
		switch($value){
			case 'Masterdocument':
				return "Procedimenti";
				break;
			case 'Document':
				return "Documenti interni ai Procedimenti";
				break;
			case self::ALL:
				return "Tutti";
				break;
			case ProcedureManager::CLOSED:
				return "Chiusi";
				break;
			case ProcedureManager::OPEN:
				return "Aperti";
				break;
			case ProcedureManager::INCOMPLETE:
				return "Incompleti";
				break;
				
		}
	}
}
?>