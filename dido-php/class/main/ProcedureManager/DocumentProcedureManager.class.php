<?php

class DocumentProcedureManager extends AProcedureManager {

	public function create($main, $data = null) {
		if (empty ( $main ))
			return false;
			
			// Deve essere fatto tutto in una singola transazione
		$this->getConnector ()->begin ();
		
		// Step 1. Salvo il record nella tabella document
		$Document = new Document ( $this->getDBConnector () );
		$result = $Document->save ( $main );
		if ($result->getErrors () !== false) {
			$this->getDBConnector ()->rollback ();
			return false;
		}
		
		$id_doc = $result->getOtherData ( 'lastInsertId' );
		$main [Document::ID_DOC] = $id_doc;
		
		// Step 2. salvo i dati associati nella tabella document_data se
		// presenti
		if (! empty ( $data )) {
			if (! $this->_saveDocData ( $data, $id_doc )) {
				$this->getDBConnector ()->rollback ();
				return false;
			}
		}
		
		$this->getDBConnector ()->commit ();
		return $main;
	}

	public function update($data) {
		if (empty ( $data ))
			return false;
			// Step 1. salvo i dati nella tabella document_data
		$id_doc = current ( array_keys ( $data ) );
		if (! $this->_saveDocData ( $data [$id_doc], $id_doc )) {
			return false;
		}
		
		return true;
	}

	public function delete($main) {
		if (empty ( $main ))
			return false;
			
			// Step 1. cancello i dati del doc
		$id_doc = $main [Document::ID_DOC];
		$Document = new Document ( $this->getDBConnector () );
		if (! $Document->delete ( $main )) {
			return false;
		}
		return true;
	}

	private function _saveDocData($data, $id_doc) {
		$DocumentData = new DocumentData ( $this->getDBconnector () );
		$result = $DocumentData->saveInfo ( $data, $id_doc, true );
		return $result->getErrors () === false;
	}
}
?>