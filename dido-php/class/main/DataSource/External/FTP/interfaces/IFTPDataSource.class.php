<?php

interface IFTPDataSource {

	public function createFolder($folder);

	public function deleteFolder($folder);
	
	public function deleteFolderRecursively($folder);

	public function deleteFile($filePath);
	
	public function getTempFile($file, $tmpPath = FILES_PATH);
	
	public function upload($source, $destination);
	
	
}

?>