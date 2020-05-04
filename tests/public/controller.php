<?php

require_once __DIR__.'/../../bootstrap.php';

session_start();

$app = $_SESSION['app'];
$message = $_REQUEST["stratus.app.{$app->getToken())}"];

$app->run($message);

$_SESSION['app'] = $app;
die();
