<?php

require_once __DIR__.'/../../bootstrap.php';

$app = require_once __DIR__.'/app.php';

session_start();
$_SESSION['app'] = $app;

echo $app;
