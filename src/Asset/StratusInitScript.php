<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Asset;

use ThenLabs\StratusPHP\AbstractApp;
use ThenLabs\StratusPHP\JavaScript\JavaScriptClassInterface;
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
        $jsVarName = $this->app->getJSVarName();

        $jsClasses = '';
        foreach ($this->app->children() as $child) {
            if ($child instanceof JavaScriptClassInterface) {
                $className = get_class($child);
                $classMembers = call_user_func([$className, 'getJavaScriptClassMembers']);

                $jsClasses .= <<<JAVASCRIPT
                    {$jsVarName}.addClass('{$className}', class {
                        {$classMembers}
                    });
                JAVASCRIPT;
            }
        }

        return <<<JAVASCRIPT
            "use strict";
            window.{$jsVarName} = new StratusApp('{$this->app->getControllerUri()}');

            {$jsClasses}
        JAVASCRIPT;
    }
}
