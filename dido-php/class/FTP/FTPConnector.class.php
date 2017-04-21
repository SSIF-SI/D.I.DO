<?php 
class FTPConnector{
	private static $_instance;
	private $_conn_id;
	private $_baseDir = null;
	private $_pdfExtensions = array('pdf','p7m');
	
	private function __construct(){
		$config = parse_ini_file("config.ini");
		$this->_conn_id = ftp_connect($config['FTP_SERVER']);
		if($this->_conn_id){
			if(!ftp_login($this->_conn_id, $config['FTP_USER_NAME'], $config['FTP_USER_PASS'])) return false;
			$this->setBaseDir($config['FTP_BASEDIR']);
		}
	}

	public function setBaseDir($baseDir){
		$this->_baseDir = "/".trim($baseDir,"/")."/";
	}
	
	public static function getInstance(){
		if(is_null(self::$_instance)){
			self::$_instance = new self();
		}	
		return self::$_instance;
	}
	
	private function __clone(){}
	private function __wakeup(){}
	
	public function file_exists( $pathFile ){
		return ftp_size($this->_conn_id, $this->_baseDir.$pathFile) > -1;
	}
	
	public function getContents($dir){
		if(self::$_instance != null){
			
			$dir = self::trim($dir);
			
			$contents = array();
			$listOfFiles = $this->_ftp_rawlist($this->_baseDir.$dir);
		
			if( count( $listOfFiles ) > 0 ){
					
				foreach ( $listOfFiles as $k => $file ) {
		
					$filename = $file[7];
					$filesize = $file[5];
					$extension = pathinfo($filename, PATHINFO_EXTENSION);
						
					$isDir = substr($file[1],0,1) == "d";
			
					
					$path_parts = pathinfo($filename);
					$ext = isset($path_parts["extension"]) ? strtolower( $path_parts["extension"] ) : "";
						
					$contents[$k] = array(
						'filename'	=> basename( $filename ),
						'size'		=> $filesize,
						'isDir'		=> $isDir,
						'isPDF'		=> in_array($ext, $this->_pdfExtensions)
					);
				}
			}
			
			$this->_sort($contents);
		
			return array(
				'path' => $dir,
				'contents'	=> $contents
			);
		}
	}
	
	private function _ftp_rawlist($dir){
		$filelist = ftp_rawlist($this->_conn_id, $dir);
		if($filelist !== false){ 
			$rawlist = join("\n", $filelist);
			preg_match_all('/^([drwx+-]{10})\s+(\d+)\s+(\w+)\s+(\w+)\s+(\d+)\s+(.{12}) (.*)$/m', $rawlist, $matches, PREG_SET_ORDER);
		
			return $this->_map($matches, 7);
		} else throw new FTPConnectorException ("Directory $dir not found");
	}
	
	private function _map( $contents, $field ){
		$newContents = array();
		foreach($contents as $k=>$file){
			$newContents[$file[$field]] = $file;
		}
		return $newContents;
	}
	
	private function _sort( &$array ){
		if(empty($array)) return $array;
	
		$dirs = array();
		$files = array();
	
		foreach( $array as $item ){
			if ( $item['isDir'] ) $dirs[] = $item;
			else $files[] = $item;
		}
	
		self::_natcasesort( $dirs );
		self::_natcasesort( $files );
	
		$array = array_merge( $dirs, $files );
	}
	
	private static function _natcasesort( &$stuff ){
	
		$newStuff = array();
	
		foreach( $stuff as $item ){
			$index = $item['desc'] . $item['filename'];
			$newStuff[$index] = $item;
		}
	
		uksort($newStuff, 'strcasecmp');
		$stuff = array_values($newStuff);
	}
	
	private static function trim($dir){
		return trim($dir,"/");
	}
	
	public function download($file = null){
		if(self::$_instance != null){
			
			//$this->cd($_SESSION[$this->_baseDir]);
			//$this->_createContentList(true);
				
			if(file_exists($file)){
				
				$path_parts = pathinfo($file['filename']);
			    $ext = isset($path_parts["extension"]) ? strtolower( $path_parts["extension"] ) : "";
				    
			    if(in_array($ext, $this->_pdfExtensions)){
			        header("Content-type: application/pdf"); // add here more headers for diff. extensions
			        header("Content-Disposition: attachment; filename=\"".$path_parts["basename"]."\""); // use 'attachment' to force a download
			    } else {
			        header("Content-type: application/octet-stream");
			        header("Content-Disposition: filename=\"".$path_parts["basename"]."\"");
			   	}
				if($file['size']< 2147483647) header("Content-length: {$file['size']}");
		   		header("Cache-control: private"); //use this to open files directly
				$result = ftp_get($this->_conn_id, "php://output", $file['filename'], FTP_BINARY);
				flush();
			} else return null;
			
		} else return null;	
	}
	
	public function getTempFile($file, $tmpPath = FILES_PATH){
		$tmpfile = $tmpPath . md5( date( "YmdHisu" ));
		$result = ftp_get( $this->_conn_id, $tmpfile, $this->_baseDir.$file, FTP_BINARY);
		return $result ? $tmpfile : false;
	}
	
	public function ftp_mksubdirs($ftpath){
		@ftp_chdir($this->_conn_id, $this->_baseDir); 
		$parts = explode(DIRECTORY_SEPARATOR, $ftpath); 
		foreach($parts as $part){
			if(!@ftp_chdir($this->_conn_id, $part)){
				$result = @ftp_mkdir($this->_conn_id, $part) && @ftp_chdir($this->_conn_id, $part);
				if(!$result) return false;
			}
		}
		return true;
	}
	
	public function upload($source, $destination){
		$result = ftp_put($this->_conn_id, $this->_baseDir . $destination, $source, FTP_BINARY);
		if(!$result) throw new Exception("Errore nel trasferimento del file", 666);
	}
	
}

class FTPConnectorException extends Exception{}
?>
