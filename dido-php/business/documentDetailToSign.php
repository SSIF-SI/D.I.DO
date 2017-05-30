<?php 
if(isset($_GET['action'])){
	$Application->manageAction($_GET['action']);
	die();
}

define (PAGE_TITLE, "Documenti da firmare");

include 'documentDetail.php';
$view = basename(__FILE__);
?>