<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Component;

use ThenLabs\StratusPHP\AbstractApp;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class Browser implements ComponentInterface
{
    protected $app;

    public function updateData(string $key, $value): void
    {
    }

    public function setApp(?AbstractApp $app): void
    {
        $this->app = $app;
    }

    public function getApp(): ?AbstractApp
    {
        return $this->app;
    }

    public static function getJavaScriptClassMembers(): string
    {
        return <<<JAVASCRIPT
        JAVASCRIPT;
    }

    public function getJavaScriptCreateInstanceScript(): string
    {
        return <<<JAVASCRIPT
        JAVASCRIPT;
    }
}
