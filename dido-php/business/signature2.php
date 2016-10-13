<?php
require_once ("../config.php");

$signers = SignatureHelper::getSigners();

include_once (TEMPLATES_PATH."template.php");