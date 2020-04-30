<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP;

use ThenLabs\ComposedViews\AbstractCompositeView;
use ThenLabs\StratusPHP\Asset\StratusScript;
use ThenLabs\StratusPHP\Asset\StratusInitScript;
use ThenLabs\StratusPHP\JavaScript\JavaScriptClassInterface;
use ReflectionClass;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 * @abstract
 */
abstract class AbstractApp extends AbstractCompositeView
{
    protected $controllerUri;
    protected $jsVarName = 'stratusAppInstance';
    protected $javaScriptClasses = [];
    protected $debug = false;

    public function __construct(string $controllerUri)
    {
        parent::__construct();

        $this->controllerUri = $controllerUri;

        $this->addFilter(function ($event) {
            $stratusScript = new StratusScript('stratus-js', null, '');

            $stratusInitScript = new StratusInitScript('stratus-init-script', null, '');
            $stratusInitScript->setApp($this);

            $event->filter('body')->append($stratusScript->render());
            $event->filter('body')->append($stratusInitScript->render());
        });
    }

    public function render(array $data = [], bool $dispatchRenderEvent = true): string
    {
        $this->updateJavaScriptClasses();

        return parent::render($data, $dispatchRenderEvent);
    }

    public function getToken(): string
    {
        return uniqid('token', true);
    }

    public function getControllerUri(): string
    {
        return $this->controllerUri;
    }

    public function setJSVarName(string $varName): void
    {
        $this->jsVarName = $varName;
    }

    public function getJSVarName(): string
    {
        return $this->jsVarName;
    }

    public function filter(string $cssSelector): Element
    {
        $element = new Element($cssSelector);

        return $element;
    }

    /**
     * @deprecated
     */
    public function hasJavaScriptClass(string $className): bool
    {
        return array_key_exists($className, $this->javaScriptClasses);
    }

    public function registerJavaScriptClass(string $className): string
    {
        $classId = $this->debug ? $className : uniqid('Class');

        $this->javaScriptClasses[$className] = $classId;

        return $classId;
    }

    public function getJavaScriptClassId(string $className): ?string
    {
        return $this->javaScriptClasses[$className] ?? null;
    }

    public function isDebug(): bool
    {
        return $this->debug;
    }

    public function setDebug(bool $value): void
    {
        $this->debug = $value;
    }

    public function getJavaScriptClasses(): array
    {
        return $this->javaScriptClasses;
    }

    protected function updateJavaScriptClasses(): void
    {
        foreach ($this->children() as $child) {
            if ($child instanceof JavaScriptClassInterface) {
                $class = new ReflectionClass($child);
                $parentClass = $class->getParentClass();
                $className = $class->getName();

                while ($parentClass) {
                    $parentClassName = $parentClass->getName();

                    if ($parentClass->implementsInterface(JavaScriptClassInterface::class) &&
                        ! isset($this->javaScriptClasses[$parentClassName])
                    ) {
                        $this->registerJavaScriptClass($parentClassName);
                    }

                    $parentClass = $parentClass->getParentClass();
                }

                if (! isset($this->javaScriptClasses[$className])) {
                    $this->registerJavaScriptClass($className);
                }
            }
        }
    }
}
