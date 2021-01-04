<?php

require_once __DIR__.'/../../bootstrap.php';
require_once __DIR__.'/Page.php';

use ThenLabs\StratusPHP\Request;
use function Opis\Closure\serialize as s;
use function Opis\Closure\unserialize as u;

session_start();

$page = u($_SESSION['page']);
$request = Request::createFromJson($_REQUEST['stratus_request']);

$result = $page->run($request);

if ($result->isSuccessful()) {
    $_SESSION['page'] = s($page);
}

die();
