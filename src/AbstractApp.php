<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP;

use ThenLabs\Components\ComponentInterface;
use ThenLabs\ComposedViews\AbstractCompositeView;
use ThenLabs\ComposedViews\Event\RenderEvent;
use ThenLabs\StratusPHP\Asset\StratusScript;
use ThenLabs\StratusPHP\Asset\StratusInitScript;
use ThenLabs\StratusPHP\Bus\StreamingBus;
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
    protected $booted = false;
    protected $bus;

    public function __construct(string $controllerUri)
    {
        parent::__construct();

        $this->controllerUri = $controllerUri;
        $this->bus = new StreamingBus;

        $this->addFilter([$this, '_addStratusAssetScripts']);
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

    public function setDebug(bool $debug): void
    {
        $this->debug = $debug;
    }

    public function isBooted(): bool
    {
        return $this->booted;
    }

    public function setBooted(bool $booted): void
    {
        $this->booted = $booted;
    }

    public function getJavaScriptClasses(): array
    {
        return $this->javaScriptClasses;
    }

    public function setJavaScriptClasses(array $javaScriptClasses): void
    {
        $this->javaScriptClasses = $javaScriptClasses;
    }

    public function querySelector(string $cssSelector): Element
    {
        $element = new Element($cssSelector);

        $this->addChild($element);

        return $element;
    }

    protected function updateJavaScriptClasses(): void
    {
        foreach ($this->children() as $child) {
            if ($child instanceof JavaScriptClassInterface) {
                $class = new ReflectionClass($child);

                $registerJavaScriptClass = function (ReflectionClass $class) use (&$registerJavaScriptClass) {
                    $parentClass = $class->getParentClass();
                    $className = $class->getName();

                    if ($parentClass &&
                        $parentClass->implementsInterface(JavaScriptClassInterface::class)
                    ) {
                        $registerJavaScriptClass($parentClass);
                    }

                    if (! isset($this->javaScriptClasses[$className])) {
                        $this->registerJavaScriptClass($className);
                    }
                };

                $registerJavaScriptClass($class);
            }
        }
    }

    public function _addStratusAssetScripts(RenderEvent $event): void
    {
        $stratusScript = new StratusScript('stratus-js', null, '');

        $stratusInitScript = new StratusInitScript('stratus-init-script', null, '');
        $stratusInitScript->setApp($this);

        $event->filter('body')->append($stratusScript->render());
        $event->filter('body')->append($stratusInitScript->render());
    }

    public function validateChild(ComponentInterface $child): bool
    {
        return true;
    }
}
