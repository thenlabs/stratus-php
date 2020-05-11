<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP;

use ThenLabs\Components\ComponentInterface;
use ThenLabs\Components\Event\BeforeInsertionEvent;
use ThenLabs\ComposedViews\AbstractCompositeView;
use ThenLabs\ComposedViews\Event\RenderEvent;
use ThenLabs\StratusPHP\Asset\StratusScript;
use ThenLabs\StratusPHP\Asset\StratusInitScript;
use ThenLabs\StratusPHP\Event\StratusEvent;
use ThenLabs\StratusPHP\Exception\FrozenViewException;
use ThenLabs\StratusPHP\Exception\InvalidTokenException;
use ThenLabs\StratusPHP\Messaging\Bus\BusInterface;
use ThenLabs\StratusPHP\Messaging\Bus\StreamingBus;
use ThenLabs\StratusPHP\Messaging\Request;
use ThenLabs\StratusPHP\Messaging\Result;
use ThenLabs\StratusPHP\JavaScript\JavaScriptClassInterface;
use Wa72\HtmlPageDom\HtmlPageCrawler;
use ReflectionClass;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 * @abstract
 */
abstract class AbstractApp extends AbstractCompositeView implements QuerySelectorInterface
{
    use SleepTrait;

    protected $controllerUri;
    protected $javaScriptClasses = [];
    protected $debug = false;
    protected $booted = false;
    protected $bus;
    protected $frozenView;
    protected $token;

    public function __construct(string $controllerUri)
    {
        parent::__construct();

        $this->controllerUri = $controllerUri;
        $this->bus = new StreamingBus;
        $this->token = uniqid('token', true);

        $this->addFilter([$this, '_addStratusAssetScripts']);
        $this->on(BeforeInsertionEvent::class, [$this, '_beforeInsertionEvent']);
    }

    public function render(array $data = [], bool $dispatchRenderEvent = true): string
    {
        $this->updateJavaScriptClasses();

        return parent::render($data, $dispatchRenderEvent);
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getControllerUri(): string
    {
        return $this->controllerUri;
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
        foreach ($this->childs as $component) {
            if ($component instanceof Element &&
                $component->getCssSelector() == $cssSelector
            ) {
                return $component;
            }
        }

        if ($this->booted) {
            $element = new Element($cssSelector);
            $element->setApp($this);

            $this->addChild($element);

            $jsElementClassId = $this->getJavaScriptClassId(Element::class);

            $data = [
                'classId' => $jsElementClassId,
                'componentId' => $element->getId(),
                'parent' => null,
                'selector' => $cssSelector,
            ];

            $this->invokeJavaScriptFunction(Element::class, 'createNew', $data);

            return $element;
        }

        $isFrozen = $this->isFrozen();

        $view = $isFrozen ? $this->frozenView : $this->render();
        $crawler = new HtmlPageCrawler($view);
        $elementCrawler = $crawler->filter($cssSelector);

        $element = new Element($cssSelector);
        $element->setCrawler($elementCrawler);
        $element->setApp($this);

        $this->addChild($element);

        if (! $isFrozen) {
            $this->frozenView = $view;
        }

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

    public function _beforeInsertionEvent(BeforeInsertionEvent $event): void
    {
        $child = $event->getChild();

        if ($this->isFrozen() && ! $child instanceof Element) {
            throw new FrozenViewException;
        }
    }

    public function validateChild(ComponentInterface $child): bool
    {
        return true;
    }

    public function isFrozen(): bool
    {
        return $this->frozenView ? true : false;
    }

    public function addFilter(callable $callback): void
    {
        if ($this->isFrozen()) {
            throw new FrozenViewException;
        }

        parent::addFilter($callback);
    }

    public function run(Request $request): Result
    {
        if (! $this->booted) {
            $this->booted = true;
        }

        if ($request->getToken() != $this->token) {
            throw new InvalidTokenException;
        }

        foreach ($request->getComponentData() as $componentId => $data) {
            $component = $this->findChildById($componentId);

            foreach ($data as $property => $value) {
                $component->{$property} = $value;
            }
        }

        $eventInfo = explode('.', $request->getEventName());
        if (count($eventInfo) == 2) {
            $componentId = $eventInfo[0];
            $eventName = $eventInfo[1];

            $event = new StratusEvent;
            $event->setApp($this);

            $component = $this->findChildById($componentId);
            $component->dispatchEvent($eventName, $event);
        }

        return new Result;
    }

    public function getBus(): BusInterface
    {
        return $this->bus;
    }

    public function invokeJavaScriptFunction(string $class, string $function, array $data): void
    {
        $this->bus->write([
            'handler' => [
                'classId' => $this->javaScriptClasses[$class],
                'method' => $function,
            ],
            'data' => $data,
        ]);
    }
}
