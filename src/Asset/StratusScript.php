<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Asset;

use ThenLabs\ComposedViews\Asset\Script;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class StratusScript extends Script
{
    public function getSource(): string
    {
        return file_get_contents(__DIR__.'/stratus.js');
    }
}
