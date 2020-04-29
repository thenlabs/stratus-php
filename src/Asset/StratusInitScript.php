<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Asset;

use ThenLabs\StratusPHP\AbstractApp;
use ThenLabs\StratusPHP\JavaScript\JavaScriptClassInterface;
use ThenLabs\StratusPHP\JavaScript\JavaScriptInstanceInterface;
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

        $classes = '';
        $instances = '';
        foreach ($this->app->children() as $child) {
            if ($child instanceof JavaScriptClassInterface) {
                $className = get_class($child);
                $classMembers = call_user_func([$className, 'getJavaScriptClassMembers']);

                $classes .= <<<JAVASCRIPT
                    \n{$jsVarName}.addClass('{$className}', class {
                        {$classMembers}
                    });\n\n
                JAVASCRIPT;

                if ($child instanceof JavaScriptInstanceInterface) {
                    $instances .= <<<JAVASCRIPT
                        \nlet ComponentClass = {$jsVarName}.getClass('{$className}');
                        {$child->getJavaScriptCreateInstance()}\n\n
                    JAVASCRIPT;
                }
            }
        }

        return <<<JAVASCRIPT
            "use strict";
            window.{$jsVarName} = new StratusApp('{$this->app->getControllerUri()}');

            {$classes}

            {$instances}
        JAVASCRIPT;
    }
}
