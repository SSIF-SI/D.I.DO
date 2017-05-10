<?php

interface IFTPDataSource {

	public function createFolder($folder);

	public function deleteFolder($folder);

	public function getNewPathFromXml($xml);

	public function getFilenameFromDocument($document);

	public function deleteFile($filePath);
}

?>