<?php
require_once 'config.php';

$Application
	->getApplicationPart(Application::IMPORT)
	->saveDataToBeImported();

?>