<?php
require_once ("java/Java.inc");
$Util = java ( "php.java.bridge.Util" );
$ctx = java_context ();
/* get the current instance of the JavaBridge, ServletConfig and Context */
$bridge = $ctx->getAttribute ( "php.java.bridge.JavaBridge", 100 );
$config = $ctx->getAttribute ( "php.java.servlet.ServletConfig", 100 );
$context = $ctx->getAttribute ( "php.java.servlet.ServletContext", 100 );
$servlet = $ctx->getAttribute ( "php.java.servlet.Servlet", 100 );

ini_set ( 'magic_quotes_gpc', 0 );
date_default_timezone_set ( 'Europe/Rome' );

// Start Session
session_start ();

// DEBUG
ini_set ( 'display_errors', 1 );
error_reporting ( E_ALL ^ E_DEPRECATED ^ E_NOTICE );

// COSTANTI
require_once ('define.php');

ini_set ( 'session.gc_probability', 0 );
ini_set ( "soap.wsdl_cache_enabled", "0" );

// AUTOLOADER
require_once (FRAMEWORK_CLASS_PATH . "Utils.class.php");
spl_autoload_register ( array (
		'Utils',
		'IncludeClass' 
) );

// LOGIN

require_once ("Auth.php");
$auth = new Auth ( HTTP_ROOT );
if (isset ( $_GET ['logout'] )) {
	Session::getInstance ()->destroy ();
	$auth->logout ();
}

//Session::getInstance()->destroy();
Session::getInstance ()->set ( AUTH_USER, $auth->getUser ( 'email' ) );
//Session::getInstance ()->set ( AUTH_USER, "claudio.montani@isti.cnr.it" );

// URI caching
TurnBack::setLastHttpReferer ();

// Trimming SCRIPT NAME
$self = explode ( "/", $_SERVER ['PHP_SELF'] );
$_SERVER ['SCRIPT_NAME'] = $self [count ( $self ) - 1];

$Application = new Application();




