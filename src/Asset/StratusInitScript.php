<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Asset;

use ThenLabs\StratusPHP\AbstractApp;
use ThenLabs\StratusPHP\JavaScript\JavaScriptClassInterface;
use ThenLabs\StratusPHP\JavaScript\JavaScriptInstanceInterface;
use ThenLabs\ComposedViews\Asset\Script;
use ReflectionClass;

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
        $jsInstances = '';

        foreach ($this->app->children() as $child) {
            if ($child instanceof JavaScriptClassInterface) {
                $class = new ReflectionClass($child);
                $className = $class->getName();

                $jsClassMembers = call_user_func([$className, 'getJavaScriptClassMembers']);
                $jsClassName = $className;
                $jsExtends = '';

                $parentClass = $class->getParentClass();
                if ($parentClass instanceof ReflectionClass &&
                    $parentClass->implementsInterface(JavaScriptClassInterface::class)
                ) {
                    $jsParentClassName = $parentClass->getName();

                    $jsClasses .= <<<JAVASCRIPT
                        \nlet ParentClass = {$jsVarName}.getClass('{$jsParentClassName}');
                    JAVASCRIPT;

                    $jsExtends = 'extends ParentClass';
                }

                $jsClasses .= <<<JAVASCRIPT
                    \n{$jsVarName}.addClass('{$jsClassName}', class {$jsExtends} {
                        {$jsClassMembers}
                    });\n\n
                JAVASCRIPT;

                if ($child instanceof JavaScriptInstanceInterface) {
                    $jsInstances .= <<<JAVASCRIPT
                        \nlet ComponentClass = {$jsVarName}.getClass('{$jsClassName}');
                        {$child->getJavaScriptCreateInstance()}\n\n
                    JAVASCRIPT;
                }
            }
        }

        return <<<JAVASCRIPT
            "use strict";
            window.{$jsVarName} = new StratusApp('{$this->app->getControllerUri()}');

            {$jsClasses}

            {$jsInstances}
        JAVASCRIPT;
    }
}
