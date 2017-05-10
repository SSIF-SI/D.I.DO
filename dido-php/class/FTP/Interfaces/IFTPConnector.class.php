<?php

interface IFTPConnector {

	public function setBaseDir($baseDir);

	public function file_exists($pathFile);

	public function getContents($dir);

	public function download($file = null);

	public function getTempFile($file, $tmpPath = FILES_PATH);

	public function mksubdirs($ftpath);

	public function deleteFolder($folder);

	public function delete($filePath);

	public function upload($source, $destination);
}
?>