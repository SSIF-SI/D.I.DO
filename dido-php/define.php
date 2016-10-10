<?php

# COSTANTI
define ("FRAMEWORK_PATH",			"/var/lib/php5/common/frameworks/myFramework/");
define ("FRAMEWORK_CLASS_PATH",		FRAMEWORK_PATH . "class/");

define ("DOCUMENT_ROOT",		dirname(__DIR__));
define ("HTTP_ROOT",			"/dido-php-test/");
define ("REAL_ROOT",			DOCUMENT_ROOT . HTTP_ROOT);
define ("INCLUDE_PATH", 		REAL_ROOT . "include/");
define ("INCLUDE_HTTP_PATH", 	HTTP_ROOT . "include/");
define ("CLASS_PATH",	 		REAL_ROOT . "class/");
define ("XML_PATH",				REAL_ROOT . "XML4Dido/");
define ("XML_MD_PATH",			XML_PATH . "master_documents/");
define ("XML_STD_PATH",			XML_PATH . "standard_documents/");

define ("SCRIPTS_PATH", 		HTTP_ROOT . "scripts/");
define ("SCRIPTS_RPATH", 		REAL_ROOT . "scripts/");
define ("BUSINESS_PATH",		REAL_ROOT . "business/");
define ("BUSINESS_HTTP_PATH",	HTTP_ROOT . "business/");
define ("AJAX_PATH",			HTTP_ROOT . "ajax/");
define ("AJAX_INCLUDE_PATH",	REAL_ROOT . "/ajax/");
define ("VIEWS_PATH", 			REAL_ROOT . "views/");
define ("COMMON_PATH", 			REAL_ROOT . "common/");
define ("ADMIN_PATH", 			REAL_ROOT . "admin/");
define ("ADMIN_BUSINESS_PATH", 	HTTP_ROOT . "admin/");
define ("ADMIN_VIEWS_PATH", 	REAL_ROOT . "views/admin/");
define ("ADMIN_SCRIPTS_PATH", 	HTTP_ROOT . "scripts/admin/");
define ("ADMIN_SCRIPTS_RPATH", 	REAL_ROOT . "scripts/admin/");

define ("FILES_PATH",			REAL_ROOT . "files/");
define ("FILES_HTTP_PATH",		HTTP_ROOT . "files/");
define ("GECO_IMPORT_PATH",		REAL_ROOT . "geco-import/");
define ("TEMPLATES_PATH",		REAL_ROOT . "templates/");
define ("SB_ADMIN_2",			HTTP_ROOT . "templates/sb-admin-2/");

define ("MAIL_FROM",			"");

define ("HOST", 				"localhost");
define ("DB_ENGINE", 			"pgsql");
define ("ROOT_USER", 			"dido");
define ("ROOT_PASSWORD", 		"emaP4ss!");
define ("ROOT_DATABASE", 		"didodb");

define ("PAGE_TITLE_PREFIX", 	"DI.DO. [TEST]");
define ("DB_DATE_FORMAT", 		"Y-m-d H:i:s");
define ("ACCESS_DENIED",		"<br><br/><p class='error'>Attenzione!! L'utente non ha i privilegi per accedere a questo contenuto.</p>");
define ("INEXISTENT",			"<br><br/><p class='error'>Attenzione!! Contenuto inesistente.</p>");

define ("FILE_LOG",				ADMIN_PATH."log/".date("Ym").".log");

define ("SITE_ONLINE",			1);

?>
