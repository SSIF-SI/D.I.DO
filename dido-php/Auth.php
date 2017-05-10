<?php

class Auth {

	const AUTH_SERVER = "http://ut6.isti.cnr.it/Auth/";

	private $_signature = null;

	private $_auth_user = null;

	private $_HTTP_ROOT = null;

	public function __construct($HTTP_ROOT = null) {
		$this->_signature = md5 ( md5 ( gethostbyname ( $_SERVER ['SERVER_ADDR'] ) ) );
		if (! isset ( $_SESSION ))
			@session_start ();
		
		if (__FILE__ == $_SERVER ['SCRIPT_FILENAME']) {
			if (isset ( $_GET ['SID'] ) && isset ( $_GET ['redirectAfter'] ) && isset ( $_GET ['signature'] ) && $_GET ['signature'] == $this->_signature) {
				$_SESSION [dirname ( __FILE__ ) . 'SID'] = $_GET ['SID'];
				$redirectAfter = rawurldecode ( $_GET ['redirectAfter'] );
				header ( "Location: $redirectAfter" );
				exit ();
			} else
				throw new AuthException ( "Bad Response", 500 );
		}
		
		$_SESSION [dirname ( __FILE__ ) . 'REQUEST'] = $_REQUEST;
		$this->_HTTP_ROOT = rawurlencode ( self::_full_uri ( $HTTP_ROOT ) );
	}

	public function getUser($what) {
		if (isset ( $_SESSION [dirname ( __FILE__ ) . 'SID'] )) {
			$cc = new cURL ( false );
			$HTML = $cc->get ( self::AUTH_SERVER . "?_AJAX&PHPSESSID=" . $_SESSION [dirname ( __FILE__ ) . 'SID'] );
			$this->_auth_user = json_decode ( $HTML );
		} else {
			$redirectAfter = rawurlencode ( self::_full_uri ( $_SERVER ['REQUEST_URI'] ) );
			$rownurl = rawurlencode ( $this->_HTTP_ROOT . basename ( __FILE__ ) );
			header ( "Location: " . self::AUTH_SERVER . "?signature={$this->_signature}&rurl=$rownurl&redirectAfter=$redirectAfter" );
			exit ();
		}
		
		$_REQUEST = $_SESSION [dirname ( __FILE__ ) . 'REQUEST'];
		
		$attribute = $this->getAttribute ( $what );
		
		if ($attribute) {
			return $attribute;
		} else {
			unset ( $_SESSION [dirname ( __FILE__ ) . 'SID'] );
			$this->getUser ( $what );
		}
	}

	public function getAttribute($a = null) {
		if (is_null ( $a ))
			return $this->_auth_user;
		if (isset ( $this->_auth_user->$a ))
			return $this->_auth_user->$a;
		return false;
	}

	public function logout() {
		unset ( $_SESSION [dirname ( __FILE__ ) . 'SID'] );
		header ( "Location: " . self::AUTH_SERVER . "?signature={$this->_signature}&logout&rurl={$this->_HTTP_ROOT}&redirectAfter={$this->_HTTP_ROOT}" );
		exit ();
	}

	private static function _full_uri($uri) {
		return "http" . (isset ( $_SERVER ['HTTPS'] ) ? "s" : "") . "://" . $_SERVER ['HTTP_HOST'] . $uri;
	}
}

if (__FILE__ == $_SERVER ['SCRIPT_FILENAME'])
	new Auth ();

class AuthException extends Exception {
}

class cURL {

	var $headers;

	var $user_agent;

	var $compression;

	var $cookie_file;

	var $proxy;

	function cURL($cookies = TRUE, $cookie = 'cookies.txt', $compression = 'gzip', $proxy = '') {
		$this->headers [] = 'Accept: image/gif, image/x-bitmap, image/jpeg, image/pjpeg';
		$this->headers [] = 'Connection: Keep-Alive';
		$this->headers [] = 'Content-type: application/x-www-form-urlencoded;charset=UTF-8';
		$this->user_agent = 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.0.3705; .NET CLR 1.1.4322; Media Center PC 4.0)';
		$this->compression = $compression;
		$this->proxy = $proxy;
		$this->cookies = $cookies;
		if ($this->cookies == TRUE)
			$this->cookie ( $cookie );
	}

	function cookie($cookie_file) {
		if (file_exists ( $cookie_file )) {
			$this->cookie_file = $cookie_file;
		} else {
			fopen ( $cookie_file, 'w' ) or $this->error ( 'The cookie file could not be opened. Make sure this directory has the correct permissions' );
			$this->cookie_file = $cookie_file;
			fclose ( $this->cookie_file );
		}
	}

	function get($url) {
		$process = curl_init ( $url );
		curl_setopt ( $process, CURLOPT_HTTPHEADER, $this->headers );
		curl_setopt ( $process, CURLOPT_HEADER, 0 );
		curl_setopt ( $process, CURLOPT_USERAGENT, $this->user_agent );
		if ($this->cookies == TRUE)
			curl_setopt ( $process, CURLOPT_COOKIEFILE, $this->cookie_file );
		if ($this->cookies == TRUE)
			curl_setopt ( $process, CURLOPT_COOKIEJAR, $this->cookie_file );
		curl_setopt ( $process, CURLOPT_ENCODING, $this->compression );
		curl_setopt ( $process, CURLOPT_TIMEOUT, 30 );
		if ($this->proxy)
			curl_setopt ( $process, CURLOPT_PROXY, $this->proxy );
		curl_setopt ( $process, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $process, CURLOPT_FOLLOWLOCATION, 1 );
		$return = curl_exec ( $process );
		curl_close ( $process );
		return $return;
	}

	function post($url, $data) {
		$process = curl_init ( $url );
		curl_setopt ( $process, CURLOPT_HTTPHEADER, $this->headers );
		curl_setopt ( $process, CURLOPT_HEADER, 1 );
		curl_setopt ( $process, CURLOPT_USERAGENT, $this->user_agent );
		if ($this->cookies == TRUE)
			curl_setopt ( $process, CURLOPT_COOKIEFILE, $this->cookie_file );
		if ($this->cookies == TRUE)
			curl_setopt ( $process, CURLOPT_COOKIEJAR, $this->cookie_file );
		curl_setopt ( $process, CURLOPT_ENCODING, $this->compression );
		curl_setopt ( $process, CURLOPT_TIMEOUT, 30 );
		if ($this->proxy)
			curl_setopt ( $process, CURLOPT_PROXY, $this->proxy );
		curl_setopt ( $process, CURLOPT_POSTFIELDS, $data );
		curl_setopt ( $process, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $process, CURLOPT_FOLLOWLOCATION, 1 );
		curl_setopt ( $process, CURLOPT_POST, 1 );
		$return = curl_exec ( $process );
		curl_close ( $process );
		return $return;
	}

	function error($error) {
		echo "<center><div style='width:500px;border: 3px solid #FFEEFF; padding: 3px; background-color: #FFDDFF;font-family: verdana; font-size: 10px'><b>cURL Error</b><br>$error</div></center>";
		die ();
	}

	function curl_load($url) {
		curl_setopt ( $ch = curl_init (), CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		$response = curl_exec ( $ch );
		curl_close ( $ch );
		return $response;
	}
}
