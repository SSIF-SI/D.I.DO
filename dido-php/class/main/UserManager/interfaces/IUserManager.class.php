<?php 
interface IUserManager{
	public function getUser();
	public function getFieldToWriteOnDb();
	public function isSigner();
	public function hasSignRole($signRole);
	public function getUserRole();
	public function getUserSign();
	public function isAdmin();
	public function isGestore($strict = false);
	public function isConsultatore($strict = false);
}
?>