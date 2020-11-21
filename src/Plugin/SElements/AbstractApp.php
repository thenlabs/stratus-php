<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Plugin\SElements;

use ThenLabs\StratusPHP\Plugin\PageDom\AbstractApp as AbstractPageDomApp;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 * @abstract
 */
abstract class AbstractApp extends AbstractPageDomApp
{
    use SElementsTrait;
}
