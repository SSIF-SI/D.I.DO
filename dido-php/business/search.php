<?php 
require_once ("../config.php");

if(Utils::checkAjax ()){
	if(isset($_GET['addFilter'])){
		include (BUSINESS_PATH."addFilter".$_GET['addFilter'].".php");
		die();
	}
}

if(isset($_GET['reset'])){
	Session::getInstance()->delete("Search_URI");
	Session::getInstance()->delete("Search_filters");
	header("Location:".$_SERVER['PHP_SELF']);
	die();
}


define (PAGE_TITLE, "Ricerca");

$Search = new Search();

if(count($_POST)){
	Utils::printr($_POST);
	if(count($_POST) == 1 && isset($_POST['postIt'])){
		Session::getInstance()->delete("Search_filters");
	} else {
		unset($_POST['postIt']);
		Session::getInstance()->set("Search_filters", $_POST);
	}
}

if(Session::getInstance()->exists("Search_URI") && $Search->getRequestUri() != Session::getInstance()->get("Search_URI")){
	header("Location:".BUSINESS_HTTP_PATH . Session::getInstance()->get("Search_URI"));
	die();
}

$pageScripts = array (
		"MyModal.js"
);

include_once (TEMPLATES_PATH . "template.php");

?>