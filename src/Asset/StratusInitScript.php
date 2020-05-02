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

        foreach ($this->app->getJavaScriptClasses() as $className => $jsClassId) {
            $jsClassMembers = call_user_func([$className, 'getJavaScriptClassMembers']);
            $jsExtends = null;
            $jsParentClass = null;

            $class = new ReflectionClass($className);
            $parentClass = $class->getParentClass();

            if ($parentClass &&
                $parentClass->implementsInterface(JavaScriptClassInterface::class)
            ) {
                $jsParentClassId = $this->app->getJavaScriptClassId($parentClass->getName());

                $jsParentClass = <<<JAVASCRIPT
                    var ParentClass = {$jsVarName}.getClass('{$jsParentClassId}');
                JAVASCRIPT;

                $jsExtends = 'extends ParentClass';
            }

            $jsClasses .= <<<JAVASCRIPT
                \n\n{$jsParentClass}
                {$jsVarName}.addClass('{$jsClassId}', class {$jsExtends} {
                    {$jsClassMembers}
                });\n
            JAVASCRIPT;
        }

        foreach ($this->app->children() as $child) {
            if ($child instanceof JavaScriptInstanceInterface) {
                $jsClassId = $this->app->getJavaScriptClassId(get_class($child));

                $jsInstances .= <<<JAVASCRIPT
                    \nvar ComponentClass = {$jsVarName}.getClass('{$jsClassId}');
                    {$child->getJavaScriptCreateInstanceScript()}\n\n
                JAVASCRIPT;
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
