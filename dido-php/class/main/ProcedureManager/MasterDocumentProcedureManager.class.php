<?php
class MasterDocumentProcedureManager extends AProcedureManager {
	/*Main RecordDB ,
	 * $main = array (
			'nome' 			=> $data['md_nome'],
			'type' 			=> $data['md_type'],
			'xml'  			=> $data['md_xml'],
			'ftp_folder'	=> $ftp_folder,
			'closed'		=> 0				
		);
	 */ 
	public function create($main, $data){
		$dbConnector=$this->getDBConnector();
		$dbConnector->begin();
		$Masterdocument = new Masterdocument($this->$dbConnector);
		$result = $Masterdocument->save($main);
		if(!empty($result->getErrors())){
			$dbConnector->rollback();
			return false;
		}
		
		$id_md = $dbConnector->getLastInsertId();
		$main['id_md'] = $id_md;
		$masterdocumentData = new MasterdocumentData($dbConnector);
		$result= $masterdocumentData->saveInfo($data, $id_md);
		if(!empty($result->getErrors())){
			$dbConnector->rollback();
			return false;
		}
		
		$dbConnector->commit();
		return $main;
	}
	/* $data
	 * [id_md]->Array()-|
	 * 				[key0]->[value0]
	 * 					...
	 * 				[keyn]->[valuen]
	 * 			
	 */
	public function update($data){
		$dbConnector=$this->getDBConnector();
		$dbConnector->begin();
		
		$id_md = current(array_keys($data));

		$masterdocumentData = new MasterdocumentData($dbConnector);
		$result = $masterdocumentData->saveInfo($data[$id_md], $id_md, true);
		if(!empty($result->getErrors())){
			$dbConnector->rollback();
			return false;
		}
		$dbConnector->commit();
		return true;
	}
	
	public function delete($main){
		$main['closed'] = self::INCOMPLETE;
		$dbConnector=$this->getDBConnector();
		
		/*
		 $dbConnector->begin();
		 $id_md = $main['id_md'];
		 $closed = array (
		 'closed' => self::INCOMPLETE
		 );
		
		 $masterdocumentData = new MasterdocumentData($dbConnector);
		 if(!$masterdocumentData->saveInfo($closed, $id_md)){
		 $dbConnector->rollback();
		 return false;
		 }
		 $dbConnector->commit();
		 return true;
		*/
		
		$Masterdocument = new Masterdocument($this->$dbConnector);
		$result = $Masterdocument->save($main);
		return empty($result->getErrors()) ? true : false;
	}
}