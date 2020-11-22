<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\JavaScript;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
interface JavaScriptClassInterface
{
    /**
     * @return string The JavaScript source code that define the members of the class.
     */
    public static function getJavaScriptClassMembers(): string;
}
