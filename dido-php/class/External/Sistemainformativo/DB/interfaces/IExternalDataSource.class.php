<?php
interface IExternalDataSource{
	private static $FILE_REGEXP = "/([a-zA-Z\s]{1,})_([a-zA-Z\s]{0,})_{0,1}([0-9]{1,})/";
	
	public function saveDataToBeImported();
	public function getDataToBeImported();
	public function import();
}
?>