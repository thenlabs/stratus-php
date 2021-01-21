<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Asset;

use ThenLabs\StratusPHP\AbstractPage;
use ThenLabs\StratusPHP\JavaScript\JavaScriptClassInterface;
use ThenLabs\StratusPHP\JavaScript\JavaScriptInstanceInterface;
use ThenLabs\ComposedViews\Asset\Script;
use MatthiasMullie\Minify;
use ReflectionClass;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class StratusInitScript extends Script
{
    use PageTrait;

    /**
     * @return string
     */
    public function getSource(): string
    {
        $jsClasses = '';
        $jsInstances = '';

        foreach ($this->page->getJavaScriptClasses() as $className => $jsClassId) {
            $jsClassMembers = call_user_func([$className, 'getJavaScriptClassMembers']);
            $jsExtends = null;
            $jsParentClass = null;

            $class = new ReflectionClass($className);
            $parentClass = $class->getParentClass();

            if ($parentClass &&
                $parentClass->implementsInterface(JavaScriptClassInterface::class)
            ) {
                $jsParentClassId = $this->page->getJavaScriptClassId($parentClass->getName());

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

        foreach ($this->page->children() as $child) {
            if ($child instanceof JavaScriptInstanceInterface) {
                $jsClassId = $this->page->getJavaScriptClassId(get_class($child));

                $jsInstances .= <<<JAVASCRIPT
                    \n{
                        const ComponentClass = app.getClass('{$jsClassId}');
                        {$child->getJavaScriptCreateInstanceScript()}
                    }\n\n
                JAVASCRIPT;
            }
        }

        $jsSetDebug = $this->page->isDebug() ? "app.debug = true;\n" : '';

        $source = <<<JAVASCRIPT
            "use strict";

            window.stratusAppInstance = new StratusApp(
                '{$this->page->getAjaxControllerUri()}',
                '{$this->page->getToken()}'
            );

            (app => {
                {$jsSetDebug}

                {$jsClasses}

                {$jsInstances}
            })(window.stratusAppInstance);
        JAVASCRIPT;

        if (! $this->page->isDebug()) {
            $source = $this->compressJavaScript($source);
        }

        return $source;
    }
}
