<?php

require_once __DIR__.'/../../bootstrap.php';
require_once __DIR__.'/App.class.php';

use ThenLabs\StratusPHP\StratusRequest;
use function Opis\Closure\serialize as s;
use function Opis\Closure\unserialize as u;

session_start();

$app = u($_SESSION['app']);
$request = StratusRequest::createFromJson($_REQUEST['stratus_request']);

$result = $app->run($request);

if ($result->isSuccessful()) {
    $_SESSION['app'] = s($app);
}

die();
