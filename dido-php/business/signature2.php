<?php
require_once ("../config.php");

/*
$signatureView =new Signature(Connector::getInstance());
$allsignatures=$signatureView->getAll("variable,sigla","id_persona");
*/

$signersObj = new Signers(Connector::getInstance());
$signers = $signersObj->getAll();
/*
foreach($signers as $k=>$signer){
	$signers[$k]['nominativo'] = Personale::getInstance()->getPersona($signature['id_persona'])['nome']." ".Personale::getInstance()->getPersona($signature['id_persona'])['cognome'];
}
*/
Utils::printr($signers);

/*
$substitutes = array();
foreach($allsignatures as $k=>$signature){
	$substitutes[$k]['id_persona'] = Personale::getInstance()->getPersona($signature['id_persona'])['nome']." ".Personale::getInstance()->getPersona($signature['id_persona'])['cognome'];
	$substitutes[$k]['id_delegato'] = Personale::getInstance()->getPersona($signature['id_delegato'])['nome']." ".Personale::getInstance()->getPersona($signature['id_delegato'])['cognome'];
	$substitutes[$k]['pkey'] = Utils::shorten($signature['pkey']);
	$substitutes[$k]['pkey_delegato'] = Utils::shorten($signature['pkey_delegato']);
}
*/

include_once (TEMPLATES_PATH."template.php");