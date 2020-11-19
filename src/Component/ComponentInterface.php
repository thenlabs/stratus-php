<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Component;

use ThenLabs\StratusPHP\AbstractApp;
use ThenLabs\StratusPHP\JavaScript\JavaScriptInstanceInterface;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
interface ComponentInterface extends JavaScriptInstanceInterface
{
    public function setApp(?AbstractApp $app): void;

    public function getApp(): ?AbstractApp;

    public function updateData(string $key, $value): void;

    public function registerCriticalProperty(string $property): void;
}
