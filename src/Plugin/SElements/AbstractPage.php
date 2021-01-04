<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Plugin\SElements;

use ThenLabs\StratusPHP\Plugin\PageDom\AbstractPage as AbstractPageDomApp;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 * @abstract
 */
abstract class AbstractPage extends AbstractPageDomApp
{
    use SElementsTrait;
}
