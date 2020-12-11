<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Component;

use ThenLabs\StratusPHP\AbstractApp;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class Browser implements ComponentInterface
{
    /**
     * @var AbstractApp
     */
    protected $app;

    /**
     * {@inheritdoc}
     */
    public function updateData(string $key, $value): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function registerCriticalData(string $dataName): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function setApp(?AbstractApp $app): void
    {
        $this->app = $app;
    }

    /**
     * {@inheritdoc}
     */
    public function getApp(): ?AbstractApp
    {
        return $this->app;
    }

    /**
     * {@inheritdoc}
     */
    public static function getJavaScriptClassMembers(): string
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getJavaScriptCreateInstanceScript(): string
    {
        return '';
    }

    /**
     * Shows a browser native alert.
     *
     * @param string $text The message text.
     */
    public function alert(string $text): void
    {
        $this->app->executeScript("alert('{$text}');", false);
    }

    /**
     * Shows a browser native confirmation.
     *
     * @param string $text The message text.
     */
    public function confirm(string $text): bool
    {
        return (bool) $this->app->executeScript("return confirm('{$text}');", true);
    }

    /**
     * Shows a browser native prompt.
     *
     * @param  string $text The message text.
     * @return string|null  The user answer.
     */
    public function prompt(string $text): ?string
    {
        return $this->app->executeScript("return prompt('{$text}');", true);
    }

    /**
     * Redirect the browser.
     *
     * @param  string $url
     */
    public function redirect(string $url): void
    {
        $this->app->executeScript("window.location.href = '{$url}';", false);
    }
}
