<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Asset;

use ThenLabs\StratusPHP\AbstractApp;
use ThenLabs\StratusPHP\JavaScript\JavaScriptClassInterface;
use ThenLabs\StratusPHP\JavaScript\JavaScriptInstanceInterface;
use ThenLabs\ComposedViews\Asset\Script;
use ReflectionClass;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class StratusInitScript extends Script
{
    /**
     * @var AbstractApp
     */
    protected $app;

    /**
     * @param AbstractApp $app
     */
    public function setApp(AbstractApp $app): void
    {
        $this->app = $app;
    }

    /**
     * @return string
     */
    public function getSource(): string
    {
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
                    var ParentClass = stratusAppInstance.getClass('{$jsParentClassId}');
                JAVASCRIPT;

                $jsExtends = 'extends ParentClass';
            }

            $jsClasses .= <<<JAVASCRIPT
                \n\n{$jsParentClass}
                stratusAppInstance.addClass('{$jsClassId}', class {$jsExtends} {
                    {$jsClassMembers}
                });\n
            JAVASCRIPT;
        }

        foreach ($this->app->children() as $child) {
            if ($child instanceof JavaScriptInstanceInterface) {
                $jsClassId = $this->app->getJavaScriptClassId(get_class($child));

                $jsInstances .= <<<JAVASCRIPT
                    \n{
                        const ComponentClass = app.getClass('{$jsClassId}');
                        {$child->getJavaScriptCreateInstanceScript()}
                    }\n\n
                JAVASCRIPT;
            }
        }

        $jsSetDebug = $this->app->isDebug() ? "app.debug = true;\n" : '';

        return <<<JAVASCRIPT
            "use strict";

            window.stratusAppInstance = new StratusApp(
                '{$this->app->getControllerUri()}',
                '{$this->app->getToken()}'
            );

            (app => {
                {$jsSetDebug}

                {$jsClasses}

                {$jsInstances}
            })(window.stratusAppInstance);
        JAVASCRIPT;
    }
}
