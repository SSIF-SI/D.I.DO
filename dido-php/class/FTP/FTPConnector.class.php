<?php

class FTPConnector implements IFTPConnector {

	const LABEL_FILENAME = "filename";

	const LABEL_SIZE = "size";

	const LABEL_ISDIR = "isDir";

	const LABEL_ISPDF = "isPDF";

	const LABEL_PATH = "path";

	const LABEL_CONTENTS = "contents";

	private $_conn_id = false;
	
	private $_FTPConfiguratorSource;

	private $_baseDir = null;

	private static $_pdfExtensions = array (
			'pdf',
			'p7m' 
	);

	private static $instance = null;
	
	public static function getInstance(){
		if(is_null(self::$instance))
			self::$instance = new self();
		return self::$instance;
	}
	
	public function __construct(AFTPConfigurationSource $FTPConfiguratorSource = null) {
		$this->_FTPConfiguratorSource =
			is_null($FTPConfiguratorSource) ? 
			new FTPConfiguratorSourceFromIniFile() :
			$FTPConfiguratorSource;
	}

	private function _connect() {
		if (! $this->_conn_id) {
			$FTPConfigurator = new FTPConfigurator ( $this->_FTPConfiguratorSource );
			
			$this->_conn_id = @ftp_connect ( $FTPConfigurator->getHost () );
			if ($this->_conn_id) {
				if (! @ftp_login ( $this->_conn_id, $FTPConfigurator->getUsername (), $FTPConfigurator->getPassword () ))
					return false;
				$this->setBaseDir ( $FTPConfigurator->getBasedir () );
			} else
				throw new FTPConnectorException ( "Unable to connect to FTP" );
		}
	}

	public function setBaseDir($baseDir) {
		$this->_baseDir = "/" . trim ( $baseDir, "/" ) . "/";
	}

	private function __clone() {
	}

	private function __wakeup() {
	}

	public function file_exists($pathFile) {
		$this->_connect ();
		return ftp_size ( $this->_conn_id, $this->_baseDir . $pathFile ) > - 1;
	}

	public function getContents($dir) {
		$this->_connect ();
		
		$dir = self::trim ( $dir );
		
		$contents = array ();
		$listOfFiles = $this->_ftp_rawlist ( $this->_baseDir . $dir );
		
		if (count ( $listOfFiles ) > 0) {
			
			foreach ( $listOfFiles as $k => $file ) {
				
				$filename = $file [7];
				$filesize = $file [5];
				$extension = pathinfo ( $filename, PATHINFO_EXTENSION );
				
				$isDir = substr ( $file [1], 0, 1 ) == "d";
				
				$path_parts = pathinfo ( $filename );
				$ext = isset ( $path_parts ["extension"] ) ? strtolower ( $path_parts ["extension"] ) : "";
				
				$contents [$k] = array (
						self::LABEL_FILENAME => basename ( $filename ),
						self::LABEL_SIZE => $filesize,
						self::LABEL_ISDIR => $isDir,
						self::LABEL_ISPDF => in_array ( $ext, self::$_pdfExtensions ) 
				);
			}
		}
		
		$this->_sort ( $contents );
		
		return array (
				self::LABEL_PATH => $dir,
				self::LABEL_CONTENTS => $contents 
		);
		
	}

	private function _ftp_rawlist($dir) {
		$this->_connect ();
		$filelist = @ftp_rawlist ( $this->_conn_id, $dir );
		if ($filelist !== false) {
			$rawlist = join ( "\n", $filelist );
			preg_match_all ( '/^([drwx+-]{10})\s+(\d+)\s+(\w+)\s+(\w+)\s+(\d+)\s+(.{12}) (.*)$/m', $rawlist, $matches, PREG_SET_ORDER );
			
			return $this->_map ( $matches, 7 );
		} else
			throw new FTPConnectorException ( "Directory $dir not found" );
	}

	private function _map($contents, $field) {
		$newContents = array ();
		foreach ( $contents as $k => $file ) {
			$newContents [$file [$field]] = $file;
		}
		return $newContents;
	}

	private function _sort(&$array) {
		if (empty ( $array ))
			return $array;
		
		$dirs = array ();
		$files = array ();
		
		foreach ( $array as $item ) {
			if ($item [self::LABEL_ISDIR])
				$dirs [] = $item;
			else
				$files [] = $item;
		}
		
		self::_natcasesort ( $dirs );
		self::_natcasesort ( $files );
		
		$array = array_merge ( $dirs, $files );
	}

	private static function _natcasesort(&$stuff) {
		$newStuff = array ();
		
		foreach ( $stuff as $item ) {
			$index = $item ['desc'] . $item ['filename'];
			$newStuff [$index] = $item;
		}
		
		uksort ( $newStuff, 'strcasecmp' );
		$stuff = array_values ( $newStuff );
	}

	private static function trim($dir) {
		return trim ( $dir, "/" );
	}

	public function download($file = null) {
		$this->_connect ();
			
		$tmpfile = $this->getTempFile ( $file );
		if (! $tmpfile)
			return null;
		
		$filesize = filesize ( $tmpfile );
		$path_parts = pathinfo ( $file );
		
		$ext = isset ( $path_parts ["extension"] ) ? strtolower ( $path_parts ["extension"] ) : "";
		
		if (in_array ( $ext, $this->_pdfExtensions )) {
			header ( "Content-type: application/pdf" ); // add here more
			                                            // headers for diff.
			                                            // extensions
			header ( "Content-Disposition: attachment; filename=\"" . $path_parts ["basename"] . "\"" ); // use
				                                                                                             // 'attachment'
				                                                                                             // to
				                                                                                             // force
				                                                                                             // a
				                                                                                             // download
		} else {
			header ( "Content-type: application/octet-stream" );
			header ( "Content-Disposition: filename=\"" . $path_parts ["basename"] . "\"" );
		}
		
		if ($filesize < 2147483647)
			header ( "Content-length: {$filesize}" );
		header ( "Cache-control: private" ); // use this to open files directly
		readfile ( $tmpfile );
		unlink ( $tmpfile );
		flush ();
		exit ();
		
	}

	public function getTempFile($file, $tmpPath = FILES_PATH) {
		$this->_connect ();
		$tmpfile = $tmpPath . md5 ( date ( "YmdHis" . microtime () ) );
		$result = @ftp_get ( $this->_conn_id, $tmpfile, $this->_baseDir . $file, FTP_BINARY );
		return $result ? $tmpfile : false;
	}

	public function mksubdirs($ftpath) {
		$this->_connect ();
		@ftp_chdir ( $this->_conn_id, $this->_baseDir );
		$parts = explode ( DIRECTORY_SEPARATOR, $ftpath );
		foreach ( $parts as $part ) {
			if (! @ftp_chdir ( $this->_conn_id, $part )) {
				if (empty ( $part ))
					continue; 
				$result = @ftp_mkdir ( $this->_conn_id, $part ) && @ftp_chdir ( $this->_conn_id, $part );
				if (! $result) {
					return false;
				}
			}
		}
		return true;
		
	}

	public function deleteFolder($folder) {
		$this->_connect ();
		return @ftp_rmdir ( $this->_conn_id, $this->_baseDir . $folder );
		
	}

	public function upload($source, $destination) {
		$this->_connect ();
		Utils::printr($destination);
		
		$result = @ftp_put ( $this->_conn_id, $this->_baseDir . $destination, $source, FTP_BINARY );
		return $result;
		
	}

	public function delete($filePath) {
		$this->_connect ();
		return @ftp_delete ( $this->_conn_id, $this->_baseDir . $filepath );
	
	}
}

class FTPConnectorException extends Exception {
}
?>
