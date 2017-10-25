<?php 
class Search{
	private $_tree = [];
	private $_requestUri; 
	private $_view;
	
	public function __construct(){
		$this->_requestUri = array_pop(explode("/",$_SERVER['REQUEST_URI']));
		$this->_view = $this->parseUri();
	}
	
	public function parseUri(){
		if(isset($_GET['source'])){
			array_push($this->_tree, $this->_translate($_GET['source']));
		} else return "search_setSource.php";
		
		if(isset($_GET['closed']) || isset($_GET['all'])){
			array_push($this->_tree, 
				isset($_GET['closed']) ? $this->_translate($_GET['closed']) : $this->_translate('all')
			);
		} else return "search_setClosed.php";
		
		return null;
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
			case 'master_documents':
				return "Procedimenti";
				break;
			case 'documents':
				return "Documenti interni ai Procedimenti";
				break;
			case 'all':
				return "Tutti";
				break;
			case '1':
				return "Chiusi";
				break;
			case '0':
				return "Aperti";
				break;
			case '-1':
				return "Incompleti";
				break;
				
		}
	}
}
?>