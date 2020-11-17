<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Plugin\PageDom;

use ThenLabs\StratusPHP\Annotation\OnConstructor;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
trait PageDomTrait
{
    /**
     * @OnConstructor
     */
    public function runPluginPageDom(): void
    {
    }
}
