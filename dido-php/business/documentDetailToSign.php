<?php 
if(isset($_GET['action'])){
	$Application->manageAction($_GET['action']);
	die();
}
include 'documentDetail.php';
$view = basename(__FILE__);
?>