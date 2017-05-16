<?php

interface IExternalDataSource {

	const FILE_REGEXP = "/([a-zA-Z\s]{1,})_([a-zA-Z\s]{0,})_{0,1}([0-9]{1,})/";

	const FILE_EXTENSION_TO_BE_IMPORTED = "tobeimported";

	const FILE_EXTENSION_TO_BE_UPDATED = "update";

	const FILE_EXTENSION_IMPORTED = "imported";

	const FILE_EXTENSION_DELETED = "deleted";

	const IMPORT_FILENAME = ImportManager::LABEL_IMPORT_FILENAME;
	
	const MD_NOME = ImportManager::LABEL_MD_NOME;
	
	const MD_TYPE = ImportManager::LABEL_MD_TYPE;
	
	const ID = "id";
	
	const NTOT = "nTot";
	
	public function saveDataToBeImported();

	public function getSavedDataToBeImported($owner, $catList, $subCategory = null);
	
	public function getExternalDocument($destinationPath, $record);
}
?>