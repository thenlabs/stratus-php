<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Component;

use ThenLabs\StratusPHP\AbstractApp;
use ThenLabs\StratusPHP\FrontCall;

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
        return '';
    }

    public function getJavaScriptCreateInstanceScript(): string
    {
        return '';
    }

    public function alert(string $text): void
    {
        $frontCall = new FrontCall(<<<JAVASCRIPT
            alert('{$text}');
        JAVASCRIPT, false);

        $this->app->executeFrontCall($frontCall);
    }

    public function confirm(string $text): bool
    {
        $frontCall = new FrontCall(<<<JAVASCRIPT
            return confirm('{$text}');
        JAVASCRIPT, false);

        return (bool) $this->app->executeFrontCall($frontCall);
    }

    public function redirect(string $url): void
    {
        $frontCall = new FrontCall(<<<JAVASCRIPT
            window.location.href = '{$url}';
        JAVASCRIPT, false);

        $this->app->executeFrontCall($frontCall);
    }
}
