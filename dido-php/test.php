<?php
require_once 'config.php';

$list = $Application
	->getApplicationPart(Application::IMPORT)
	->saveDataToBeImported();
		
//$list[Application_DocumentBrowser::LABEL_MD] = Common::categorize($list[Application_DocumentBrowser::LABEL_MD]);

//Utils::printr($list[Application_DocumentBrowser::LABEL_MD]);

//Utils::printr(ArrayHelper::countItems($list, "ordine cassa"));