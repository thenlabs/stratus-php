<?php

require_once __DIR__.'/../../bootstrap.php';

session_start();
$app = $_SESSION['app'];

$app->handle($_REQUEST["stratus.app.{$app->getToken())}"]);

$_SESSION['app'] = $app;
die();