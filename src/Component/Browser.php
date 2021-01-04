<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Component;

use ThenLabs\StratusPHP\AbstractPage;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class Browser implements ComponentInterface
{
    /**
     * @var AbstractPage
     */
    protected $page;

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
    public function setPage(?AbstractPage $page): void
    {
        $this->page = $page;
    }

    /**
     * {@inheritdoc}
     */
    public function getPage(): ?AbstractPage
    {
        return $this->page;
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
        $this->page->executeScript("alert('{$text}');", false);
    }

    /**
     * Shows a browser native confirmation.
     *
     * @param string $text The message text.
     */
    public function confirm(string $text): bool
    {
        return (bool) $this->page->executeScript("return confirm('{$text}');", true);
    }

    /**
     * Shows a browser native prompt.
     *
     * @param  string $text The message text.
     * @return string|null  The user answer.
     */
    public function prompt(string $text): ?string
    {
        return $this->page->executeScript("return prompt('{$text}');", true);
    }

    /**
     * Redirect the browser.
     *
     * @param  string $url
     */
    public function redirect(string $url): void
    {
        $this->page->executeScript("window.location.href = '{$url}';", false);
    }
}
