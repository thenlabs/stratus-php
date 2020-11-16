<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP;

use ThenLabs\StratusPHP\JavaScript\JavaScriptInstanceInterface;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
interface ComponentInterface extends JavaScriptInstanceInterface
{
    public function updateData(string $key, $value): void;

    public function setApp(?AbstractApp $app): void;

    public function getApp(): ?AbstractApp;
}
