<?php

require_once __DIR__.'/../../bootstrap.php';

if (isset($_GET['data'])) {
    extract(unserialize($_GET['data']));
}

$app = require_once __DIR__.'/app.php';

session_start();
$_SESSION['app'] = serialize($app);

echo $app;
