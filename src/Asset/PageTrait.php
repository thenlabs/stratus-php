<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Asset;

use ThenLabs\StratusPHP\AbstractPage;
use MatthiasMullie\Minify;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
trait PageTrait
{
    /**
     * @var AbstractPage
     */
    protected $page;

    /**
     * @param AbstractPage $page
     */
    public function setPage(AbstractPage $page): void
    {
        $this->page = $page;
    }

    /**
     * Compress JavaScript code.
     *
     * @param  string $javaScript
     * @return string
     */
    public function compressJavaScript(string $javaScript): string
    {
        $minimizer = new Minify\JS($javaScript);

        return $minimizer->minify();
    }
}
