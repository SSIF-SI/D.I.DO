<?php 

require_once ('define.php');

// DEBUG
ini_set ( 'display_errors', 1 );
error_reporting ( E_ALL ^ E_DEPRECATED ^ E_NOTICE );

// AUTOLOADER
require_once (FRAMEWORK_CLASS_PATH . "Utils.class.php");
spl_autoload_register ( array (
		'Utils',
		'IncludeClass'
) );

$SD = new SignatureDispatcher(new FTPDataSource());
/*
var_dump($SD->dispatch("DIR", "pec/2017/11/attività_pec_114/atto_145.pdf"));
var_dump($SD->put("atto_145______#pec#2017#11#attività_pec_114#atto_145_signed.pdf"));
*/
$SD->scan();

?>
