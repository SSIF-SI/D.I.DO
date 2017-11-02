<?php

class MasterDocumentProcedureManager extends AProcedureManager {

	/*
	 * Main RecordDB ,
	 * $main = array (
	 * 'nome' => $data['md_nome'],
	 * 'type' => $data['md_type'],
	 * 'xml' => $data['md_xml'],
	 * 'ftp_folder' => $ftp_folder,
	 * 'closed' => 0
	 * );
	 */
	public function create($main, $data) {
		if (empty ( $main ) || empty ( $data ))
			return false;
		
		$this->getDbConnector ()->begin ();
		
		$Masterdocument = new Masterdocument ( $this->getDbConnector () );
		$result = $Masterdocument->save ( $main );
		
		if ($result->getErrors () !== false) {
			$this->getDbConnector ()->rollback ();
			return false;
		}
		
		$id_md = $this->getDbConnector ()->getLastInsertId ();
		$main [Masterdocument::ID_MD] = $id_md;
		
		$masterdocumentData = new MasterdocumentData ( $this->getDbConnector () );
		$result = $masterdocumentData->saveInfo ( $data, $id_md );
		if ($result->getErrors () !== false) {
			$this->getDbConnector ()->rollback ();
			return false;
		}
		
		$this->getDbConnector ()->commit ();
		return $main;
	}

	/*
	 * $data
	 * [id_md]->Array()-|
	 * [key0]->[value0]
	 * ...
	 * [keyn]->[valuen]
	 *
	 */
	public function update($data) {
		if (empty ( $data ))
			return false;
		$this->getDbConnector ()->begin ();
		
		$id_md = current ( array_keys ( $data ) );
		
		$masterdocumentData = new MasterdocumentData ( $this->getDbConnector () );
		$result = $masterdocumentData->saveInfo ( $data [$id_md], $id_md, true );
		if ($result->getErrors () !== false) {
			$this->getDbConnector ()->rollback ();
			return false;
		}
		$this->getDbConnector ()->commit ();
		return true;
	}

	public function close($main,$status) {
		if (empty ( $main ))
			return false;
		
		$Masterdocument = new Masterdocument ( $this->getDbConnector () );
		
		$stub = $Masterdocument->getStub();
		$main [Masterdocument::CLOSED] = $status;
		
		$md = Utils::stubFill($stub, $main);
		
		$result = $Masterdocument->save ( $md );
		return $result->getErrors () === false ? false : true;
	}
	
	public function delete($main) {
		if (empty ( $main ))
			return false;
		//Cancello dal DB il record del master document in Cascade anche MASTERDOCUMENT_DATA, DOCUMENT, DOCUMENT_DATA
		$Masterdocument = new Masterdocument ( $this->getDbConnector () );
		$result = $Masterdocument->delete($object);
		
		return $result->getErrors () === false ? true : false;
	}
}