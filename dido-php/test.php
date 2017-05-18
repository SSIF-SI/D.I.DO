<?php
require_once 'config.php';

Utils::printr($Application
	->getApplicationPart(Application::DOCUMENTBROWSER)
	->getAllMyPendingsDocument()
);
