<?php

require_once __DIR__.'/../../bootstrap.php';

use function Opis\Closure\{serialize as s, unserialize as u};

require_once __DIR__.'/App.class.php';

session_start();

$app = u($_SESSION['app']);
$message = json_decode($_REQUEST['stratus_message'], true);

$app->run($message);

$_SESSION['app'] = s($app);
die();
