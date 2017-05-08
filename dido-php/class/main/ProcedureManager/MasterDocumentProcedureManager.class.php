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
	public function create($main, $data, $uploadPath){
		$dbConnector=$this->getDBConnector();
		$dbConnector->begin();
		$Masterdocument = new Masterdocument($this->$dbConnector);
		$result = $Masterdocument->save($main);
		if(!empty($result['errors'])){
			$dbConnector->rollback();
			return false;
		}
		
		$id_md = $dbConnector->getLastInsertId();
		$masterdocumentData = new MasterdocumentData($dbConnector);
			$result= $masterdocumentData->saveInfo($data, $id_md);
		if(!empty($result['errors'])){
			$dbConnector->rollback();
			return false;
		}
		
		$ftp_folder = dirname($main['xml']).DIRECTORY_SEPARATOR.date("Y").DIRECTORY_SEPARATOR.date("m");
			$ftpPath=$ftp_folder . DIRECTORY_SEPARATOR.$main['nome'] . "_" . $id_md;
			if (!$this->getFTPConnector()->ftp_mksubdirs($ftpPath)){
				$result['errors'] = "Cannot create subdirs in FTP";
				$dbConnector->rollback();
				return false;
			}
		$dbConnector->commit();
		return true;
	}
	/* $data
	 * [id_md]->Array()-|
	 * 				[key0]->[value0]
	 * 					...
	 * 				[keyn]->[valuen]
	 * 			
	 */
	public function update($data, $uploadPath = null){
		$dbConnector=$this->getDBConnector();
		$dbConnector->begin();
		
		$id_md = current(array_keys($data));

		$masterdocumentData = new MasterdocumentData($dbConnector);
		$result = $masterdocumentData->saveInfo($data[$id_md], $id_md, true);
		if(!empty($result['errors'])){
			$dbConnector->rollback();
			return false;
		}
		$dbConnector->commit();
		return true;
	}
	
	public function delete($main){
		$dbConnector=$this->getDBConnector();
		$dbConnector->begin();
		
		$id_md = $main['id_md'];
		$closed = array (
				'closed' => INCOMPLETE
		);
		
		$masterdocumentData = new MasterdocumentData($dbConnector);	
		if(!$masterdocumentData->saveInfo($closed, $id_md)){
			$dbConnector->rollback();
			return false;
		}
		$dbConnector->commit();
		return true;
		
	}
	
}