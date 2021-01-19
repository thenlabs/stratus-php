<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Asset;

use ThenLabs\ComposedViews\Asset\Script;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class StratusScript extends Script
{
    use PageTrait;

    /**
     * @return string
     */
    public function getSource(): string
    {
        $source = file_get_contents(__DIR__.'/stratus.js');

        if (! $this->page->isDebug()) {
            $source = $this->compressJavaScript($source);
        }

        return $source;
    }
}
