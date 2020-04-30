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
            $className = get_class($child);

            if ($child instanceof JavaScriptClassInterface &&
                ! $this->app->hasJavaScriptClass($className)
            ) {
                $jsClassId = $this->app->registerJavaScriptClass($className);
                $jsClasses .= $this->getJavaScriptClassDefinition($className, $jsClassId, $jsVarName);
            }

            if (! isset($jsClassId)) {
                $jsClassId = $this->app->getJavaScriptClassId($className);
            }

            if ($child instanceof JavaScriptInstanceInterface) {
                $jsInstances .= <<<JAVASCRIPT
                    \nvar ComponentClass = {$jsVarName}.getClass('{$jsClassId}');
                    {$child->getJavaScriptCreateInstance()}\n\n
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

    private function getJavaScriptClassDefinition(string $className, string $jsClassId, string $jsVarName): string
    {
        $result = '';

        $class = new ReflectionClass($className);
        $parentClass = $class->getParentClass();

        $jsClassMembers = call_user_func([$className, 'getJavaScriptClassMembers']);
        $jsExtends = '';

        if ($parentClass instanceof ReflectionClass &&
            $parentClass->implementsInterface(JavaScriptClassInterface::class)
        ) {
            $parentClassName = $parentClass->getName();

            if (! $this->app->hasJavaScriptClass($parentClassName)) {
                $jsParentClassId = $this->app->registerJavaScriptClass($parentClassName);
                $result .= $this->getJavaScriptClassDefinition($parentClassName, $jsParentClassId, $jsVarName);
            }

            if (! isset($jsParentClassId)) {
                $jsParentClassId = $this->app->getJavaScriptClassId($parentClassName);
            }

            $result .= <<<JAVASCRIPT
                \nvar ParentClass = {$jsVarName}.getClass('{$jsParentClassId}');
            JAVASCRIPT;

            $jsExtends = 'extends ParentClass';
        }

        $result .= <<<JAVASCRIPT
            \n{$jsVarName}.addClass('{$jsClassId}', class {$jsExtends} {
                {$jsClassMembers}
            });\n\n
        JAVASCRIPT;

        return $result;
    }
}
