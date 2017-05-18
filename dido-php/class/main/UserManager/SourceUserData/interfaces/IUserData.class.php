<?php

interface IUserData {

	public function getUid();

	public function getNome();

	public function getCognome();

	public function getEmail();

	public function getCodiceFiscale();

	public function getGruppi();

	public function getprogetti();
	
	public function reload();
}
?>