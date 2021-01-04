    <?php

    use ThenLabs\StratusPHP\Tests\SeleniumTestCase;
use ThenLabs\StratusPHP\Plugin\SElements\AbstractPage as TestApp;
use ThenLabs\StratusPHP\Annotation\EventListener;


    require_once 'App.class.php';

    $page = new App('/controller.php');
    $page->setDebug(true);
    $page->setJavaScriptClasses(array (
  'ThenLabs\\StratusPHP\\Plugin\\PageDom\\Element' => 'Class5ff31866f41fa',
));

    


    return $page;