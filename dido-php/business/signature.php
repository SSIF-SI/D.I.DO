<?php
require_once ("../config.php");

$signers = SignatureHelper::getSigners();
$newsigner= SignatureHelper::getNewSigner();

include_once (TEMPLATES_PATH."template.php");