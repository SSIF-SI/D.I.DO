<?php 
class DocumentProcedureManager extends AProcedureManager{
	
	public function create($main, $data){
		// Deve essere fatto tutto in una singola transazione
		$this->getConnector()->begin();
		
		// Step 1. Salvo il record nella tabella document
		$Document = new Document($this->getDBConnector());
		$result = $Document->save($main);
		if(!empty($result->getErrors())){
			$this->getDBConnector()->rollback();
			return false;
		}
		
		// Step 2. salvo i dati associati nella tabella document_data
		$id_doc = $result->getOtherData('lastInsertId');
		$main['id_doc'] = $id_doc;
		if(!$this->_saveDocData($data, $id_doc)){
			$this->getDBConnector()->rollback();
			return false;
		}
		
		$this->getDBConnector()->commit();
		 
		return $main;
	}
	
	public function update($data){
		// Step 1. salvo i dati nella tabella document_data
		$id_doc = current(array_keys($data));
		if(!$this->_saveDocData($data[$id_doc], $id_doc)){
			return false;
		}
		
		return true;
	}
	
	public function delete($main, $ftpFilePath){
		// Step 1. cancello i dati del doc
		$id_doc = $main['id_doc'];
		$Document = new Document($this->getDBConnector());
		if(!$Document->delete($main)){
			return false;
		}
		return true;
	}
	
	private function _saveDocData($data, $id_doc){
		$DocumentData = new DocumentData($this->getDBconnector());
		$result = $DocumentData->saveInfo($data, $id_doc, true);
		return !empty($result->getErrors());
	}

	private function _checkResult($result){
		
	}
}
?>