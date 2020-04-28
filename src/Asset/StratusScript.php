<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Asset;

use ThenLabs\ComposedViews\Asset\Script;

class StratusScript extends Script
{
    protected $attributes = [];

    public function getSource(): string
    {
        return file_get_contents(__DIR__.'/stratus.js');
    }
}
