<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\JavaScript;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
interface JavaScriptInstanceInterface extends JavaScriptClassInterface
{
    public function getJavaScriptCreateInstanceScript(): string;
}
