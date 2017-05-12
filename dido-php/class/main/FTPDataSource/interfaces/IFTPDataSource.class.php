<?php

interface IFTPDataSource {

	public function createFolder($folder);

	public function deleteFolder($folder);

	public function getNewPathFromXml($xml);

	public function getFilenameFromDocument($document);
	public function getFolderNameFromMasterdocument($md);

	public function deleteFile($filePath);
	
	public function getTempFile($file, $tmpPath = FILES_PATH);
	
	public function upload($file, $tmpPath = FILES_PATH);
}

?>