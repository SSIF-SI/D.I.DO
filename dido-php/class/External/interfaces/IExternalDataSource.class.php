<?php
interface IExternalDataSource{
	const FILE_REGEXP = "/([a-zA-Z\s]{1,})_([a-zA-Z\s]{0,})_{0,1}([0-9]{1,})/";
	const FILE_EXTENSION_TO_BE_IMPORTED = "tbi";
	const FILE_EXTENSION_IMPORTED = "imported";
	const FILE_EXTENSION_DELETED = "deleted";
	
	public function saveDataToBeImported();
	public function getSavedDataToBeImported($owner, $catList);
}
?>