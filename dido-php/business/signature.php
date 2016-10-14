<?php
require_once ("../config.php");

$signers = SignatureHelper::getSigners();
$newsigner= SignatureHelper::createModalSigner();

include_once (TEMPLATES_PATH."template.php");