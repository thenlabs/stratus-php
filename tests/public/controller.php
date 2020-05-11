<?php

require_once __DIR__.'/../../bootstrap.php';
require_once __DIR__.'/App.class.php';

use ThenLabs\StratusPHP\Messaging\Request;

session_start();

$app = unserialize($_SESSION['app']);
$request = Request::createFromJson($_REQUEST['stratus_request']);

$result = $app->run($request);

if ($result->isSuccessful()) {
    $_SESSION['app'] = serialize($app);
}

die();
