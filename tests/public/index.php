<?php

require_once __DIR__.'/../../bootstrap.php';

use function Opis\Closure\serialize as s;

if (isset($_GET['data'])) {
    extract(unserialize($_GET['data']));
}

$page = require_once __DIR__.'/page.php';

session_start();
$_SESSION['page'] = s($page);

echo $page;
