<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Asset;

use ThenLabs\StratusPHP\AbstractApp;
use ThenLabs\ComposedViews\Asset\Script;

class StratusInitScript extends Script
{
    protected $app;

    public function setApp(AbstractApp $app): void
    {
        $this->app = $app;
    }

    public function getSource(): string
    {
        return <<<JAVASCRIPT
            window.{$this->app->getJSVarName()} = new StratusApp('{$this->app->getControllerUri()}');
        JAVASCRIPT;
    }
}
