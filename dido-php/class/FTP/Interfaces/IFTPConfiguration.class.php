<?php 
interface IFTPConfiguration{
	function getHost();
	function getUsername();
	function getPassword();
	function getBasedir();
	function isActive();
}
?>
