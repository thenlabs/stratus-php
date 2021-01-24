
# How to use StratusPHP?.

**Install StratusPHP.**

    $ composer require thenlabs/stratus-php 1.0.x-dev

**Create a controller that instantiate the page, persist it and returns the view.**

```php
<?php
// public/index.php

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/../src/MyPage.php';

use function Opis\Closure\serialize as s;

// Creates the page instance specifying the url where will be processing the requests.
$page = new MyPage('/ajax.php');

// persists the instance on the session(in this case).
session_start();
$_SESSION['page'] = s($page);

// returns the view of the page.
echo $page;
```

**Create the controller that will handle asynchronous requests.**

```php
<?php
// public/ajax.php

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/../src/MyPage.php';

use ThenLabs\StratusPHP\Request;
use function Opis\Closure\{serialize as s, unserialize as u};

// Gets the persisted page instance.
session_start();
$page = u($_SESSION['page']);

// Do process the request.
$request = Request::createFromJson($_REQUEST['stratus_request']);
$result = $page->run($request);

// If the processing was successful, persist the page again.
if ($result->isSuccessful()) {
    $_SESSION['page'] = s($page);
}

die();
```

**Create the page class.**

[See examples](examples/index.md)