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
	Session::getInstance()->delete("Search_postIt");
	header("Location:".$_SERVER['PHP_SELF']);
	die();
}

if(isset($_GET[Masterdocument::ID_MD])){
	include(BUSINESS_PATH."document.php");
	die();
}

if(isset($_GET['keyword'])){
	$className = $_GET [Search::SOURCE];
	$dataclassName = $className . "Data";
	
	$A = new Application ();
	$D = new $dataclassName ( $A->getDBConnector () );
	$D->useView ( true );
	$where=$dataclassName::KEY." ilike '".$_GET['keyword']."'";
	
	if(isset($_GET['term'])){
		$term=$_GET['term'];
		if(isset ($_GET ["transform"])){
			$transformlist=ListHelper::$_GET ["transform"]();
			$transformlist=array_filter($transformlist, function($el) use ($term) {
				return ( stripos($el, $term) !== false );
			});
			$key=array_map(function($el){ return "'".$el."'";}, array_keys($transformlist));
			$where=$where." AND ". $dataclassName::VALUE." IN ( ".implode(", ", $key ). " ) ";				
		}else{
			$where=$where." AND ". $dataclassName::VALUE." ilike '%".$_GET['term']."%'";
		}
	}
	if( $_GET [SharedDocumentConstants::CLOSED]){
			$where=$where." AND ".SharedDocumentConstants::CLOSED . "=" . $_GET [SharedDocumentConstants::CLOSED];
		}	
	
	$listkeyValues= $D->getRealDistinct ( $dataclassName::VALUE, $where, $dataclassName::VALUE );
	$listkeyValues = Utils::getListfromField ( $listkeyValues, $dataclassName::VALUE,$dataclassName::VALUE);
	if(isset ($_GET ["transform"])){
	 $tmp=array();
	 foreach($listkeyValues as $k=>$val){
	 	if(isset($transformlist[$val]))
	 		array_push($tmp,array("value"=>$transformlist[$val], "key"=>$val));
	}
	$listkeyValues=$tmp;
	}
	die(json_encode($listkeyValues));
}



define (PAGE_TITLE, "Ricerca");

$Search = new Search();

if(count($_POST) || Session::getInstance()->exists("Search_postIt")){
	if(count($_POST)){
		if(isset($_POST['postIt'])){
			
			Session::getInstance()->set("Search_postIt",true);
			
			if(count($_POST) == 1 && isset($_POST['postIt'])){
				Session::getInstance()->delete("Search_filters");
			} else {
				unset($_POST['postIt']);
				Session::getInstance()->set("Search_filters", $_POST);
			}
		}
	}
	
	$filters = Session::getInstance()->exists("Search_filters") ? Session::getInstance()->get("Search_filters") : null;
		
	$source = $_GET['source'];
	$closed = isset($_GET['closed']) ? $_GET['closed'] : null;
	
	$types = isset($filters['nome']) ? array_map("strtolower",$filters['nome']) : null;
	$keywords = isset($filters['keyword']) ? $filters['keyword'] : null;
	
	$list = $Application
		->getApplicationPart(Application::DOCUMENTBROWSER)
		->searchDocuments($source, $closed, $types,$keywords);
	
	$list[Application_DocumentBrowser::LABEL_MD] = Common::categorize($list[Application_DocumentBrowser::LABEL_MD]);
	
	$XMLDataSource = $Application->getXMLDataSource();
}



$pageScripts = array ("MyModal.js","locationHash.js");

include_once (TEMPLATES_PATH . "template.php");

?>