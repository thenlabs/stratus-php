<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Plugin\PageDom;

use ThenLabs\StratusPHP\AbstractApp as AbstractStratusApp;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 * @abstract
 */
abstract class AbstractApp extends AbstractStratusApp
{
    use PageDomTrait;
}
