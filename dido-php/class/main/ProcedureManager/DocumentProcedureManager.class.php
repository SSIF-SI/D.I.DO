<?php 
class DocumentProcedureManager extends AProcedureManager{
	
	public function create($main, $data, $uploadPath){
		// Deve essere fatto tutto in una singola transazione
		$this->getConnector()->begin();
		
		// Step 1. Salvo il record nella tabella document
		$Document = new Document($this->getDBConnector());
		$result = $Document->save($main);
		if(!empty($result['errors'])){
			$this->getDBConnector()->rollback();
			return false;
		}
		
		// Step 2. salvo i dati associati nella tabella document_data
		$id_doc = $result['lastInsertId'];
		if(!$this->_saveDocData($data, $id_doc)){
			$this->getDBConnector()->rollback();
			return false;
		}
		
		
		// Step.3 Se tutto ok faccio l'upload del file
		$result = $this->_upload($uploadPath);
		
		return $this->_checkResult($result);
	}
	
	public function update($data, $uploadPath = null){
		// Deve essere fatto tutto in una singola transazione
		$this->getConnector()->begin();
		
		// Step 1. salvo i dati nella tabella document_data
		$id_doc = current(array_keys($data));
		if(!$this->_saveDocData($data[$id_doc], $id_doc)){
			$this->getDBConnector()->rollback();
			return false;
		}
		
		// Step 2. se tutto ok faccio eventualmente l'upload del file
		$result = $this->_upload($uploadPath);
		
		return $this->_checkResult($result);
	}
	
	public function delete($main, $ftpFilePath){
		// Deve essere fatto tutto in una singola transazione
		$this->getConnector()->begin();
	
		// Step 1. cancello i dati del doc
		$id_doc = $main['id_doc'];
		$Document = new Document($this->getDBConnector());
		if(!$Document->delete($main)){
			$this->getDBConnector()->rollback();
			return false;
		}
		
		// Step 2. se tutto ok cancello il file
		$result = $this->getFTPConnector()->delete($ftpFilePath);
		
		return $this->_checkResult($result);
	}
	
	private function _saveDocData($data, $id_doc){
		$DocumentData = new DocumentData($this->getDBConnector());
		$result = $DocumentData->saveInfo($data, $id_doc, true);
		return !empty($result['errors']);
	}
	
	private function _upload($uploadPath){
		return 
			!is_null($uploadPath) ?
			$this->getFTPConnector()->upload($uploadPath['source'], $uploadPath['destination']) :
			true;
	}
	
	private function _checkResult($result){
		$result ?
			$this->getDBConnector()->commit() :
			$this->getDBConnector()->rollback();
			
		return $result;
	}
}
?>