<?php 
class Application{
	/*
	 * La classe principale di DIDO
	 * 
	 * Dido gestisce i dati di 3 sorgenti principali:
	 * 
	 * - Repository FTP
	 * - Database 
	 * - Dati importati da altri sistemi
	 *
	 * L'accesso a Dido è riservato agli utenti ISTI con vari livelli di permessi.
	 * 
	 */
	
	/*
	 * Sorgenti di dati
	 */
	private $_FTP_Data_Source;
	private $_DB_Data_Source;
	private $_External_Data_Source;
	
	/*
	 * Dati dell'utente collegato
	 */
	private $_UserManager;
	
	public function __construct(){
		$UManager = new UserManager();
	}
	
	
	
}
?>