<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP;

use ThenLabs\Components\ComponentInterface;
use ThenLabs\Components\CompositeComponentInterface;
use ThenLabs\Components\Event\BeforeInsertionEvent;
use ThenLabs\ComposedViews\AbstractCompositeView;
use ThenLabs\ComposedViews\Event\RenderEvent;
use ThenLabs\ComposedViews\Asset\Script;
use ThenLabs\StratusPHP\Annotation\OnConstructor;
use ThenLabs\StratusPHP\Asset\StratusScript;
use ThenLabs\StratusPHP\Asset\StratusInitScript;
use ThenLabs\StratusPHP\Component\ComponentInterface as StratusComponentInterface;
use ThenLabs\StratusPHP\Event\Event;
use ThenLabs\StratusPHP\Event\SleepChildEvent;
use ThenLabs\StratusPHP\Exception\InmutableViewException;
use ThenLabs\StratusPHP\Exception\InvalidTokenException;
use ThenLabs\StratusPHP\Exception\FrontCallException;
use ThenLabs\StratusPHP\Bus\BusInterface;
use ThenLabs\StratusPHP\Bus\StreamingBus;
use ThenLabs\StratusPHP\JavaScript\JavaScriptClassInterface;
use ThenLabs\StratusPHP\JavaScript\JavaScriptUtils;
use ThenLabs\StratusPHP\Plugin\PageDom\Element;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Wa72\HtmlPageDom\HtmlPageCrawler;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\AnnotationReader;
use ReflectionClass;

AnnotationRegistry::registerFile(__DIR__.'/Annotation/EventListener.php');
AnnotationRegistry::registerFile(__DIR__.'/Annotation/OnConstructor.php');

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 * @abstract
 */
abstract class AbstractApp extends AbstractCompositeView
{
    protected $controllerUri;
    protected $javaScriptClasses = [];
    protected $classListWithTotalInsertionCapability = [];
    protected $debug = false;
    protected $booted = false;
    protected $bus;
    protected $inmutableView;
    protected $token;
    protected $currentRequest;

    public function __construct(string $controllerUri)
    {
        parent::__construct();

        $this->controllerUri = $controllerUri;
        $this->bus = new StreamingBus;
        $this->token = uniqid('token', true);

        $this->addFilter([$this, '_addStratusAssetScripts']);
        $this->on(BeforeInsertionEvent::class, [$this, '_beforeInsertionEvent']);

        $this->registerJavaScriptClass(JavaScriptUtils::class);

        $class = new ReflectionClass($this);
        $annotationReader = new AnnotationReader;

        foreach ($class->getMethods() as $method) {
            if ($annotation = $annotationReader->getMethodAnnotation($method, OnConstructor::class)) {
                call_user_func([$this, $method->getName()]);
            }
        }
    }

    public function getOwnDependencies(): array
    {
        $stratusScript = new StratusScript('stratus-js', null, '');

        $stratusInitScript = new StratusInitScript('stratus-init-script', null, '');
        $stratusInitScript->setApp($this);

        return compact('stratusScript', 'stratusInitScript');
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
        $body = $event->filter('body');

        foreach ($this->getOwnDependencies() as $dependency) {
            if ($dependency instanceof Script) {
                $body->append($dependency->render());
            }
        }
    }

    public function _beforeInsertionEvent(BeforeInsertionEvent $event): void
    {
        $child = $event->getChild();

        if ($this->hasInmutableView() &&
            ! in_array(get_class($child), $this->classListWithTotalInsertionCapability)
        ) {
            throw new InmutableViewException;
        }

        if ($child instanceof StratusComponentInterface) {
            $child->setApp($this);
        }
    }

    public function validateChild(ComponentInterface $child): bool
    {
        return true;
    }

    public function hasInmutableView(): bool
    {
        return $this->inmutableView ? true : false;
    }

    public function addFilter(callable $callback): void
    {
        if ($this->hasInmutableView()) {
            throw new InmutableViewException;
        }

        parent::addFilter($callback);
    }

    public function run(Request $request): Response
    {
        if (! $this->booted) {
            $this->booted = true;
        }

        if ($request->getToken() != $this->token) {
            throw new InvalidTokenException;
        }

        $this->currentRequest = $request;

        foreach ($request->getComponentData() as $componentId => $componentDataList) {
            $component = $this->findChildById($componentId);

            if ($component instanceof ComponentInterface &&
                is_array($componentDataList)
            ) {
                foreach ($componentDataList as $key => $value) {
                    $component->updateData($key, $value);
                }
            }
        }

        $eventInfo = explode('.', $request->getEventName());
        $response = new Response;

        if (count($eventInfo) == 2) {
            $componentId = $eventInfo[0];
            $eventName = $eventInfo[1];

            $component = $this->findChildById($componentId);
        } else {
            $eventName = $eventInfo[0];

            $component = $this;
        }

        $event = new Event;
        $event->setApp($this);
        $event->setSource($component);
        $event->setEventData($eventData = $request->getEventData());

        try {
            if ($request->isCapture()) {
                $targetCrawler = new HtmlPageCrawler($eventData['target']['innerHTML']);
                $targetElement = new Element('');
                $targetElement->setCrawler($targetCrawler);
                $targetElement->setProperties($eventData['target']);

                $event->setTarget($targetElement);

                $component->getCaptureEventDispatcher()->dispatch($eventName, $event);
            } else {
                $component->dispatchEvent($eventName, $event);
            }
        } catch (FrontCallException $exception) {
            $response->setSuccessful(false);

            $frontCall = $exception->getFrontCall();

            $this->bus->write([
                'resend' => true,
                'executedFrontCalls' => $request->getExecutedFrontCalls(),
                'frontCall' => [
                    'hash' => $frontCall->getHash(),
                    'script' => $frontCall->getScript(),
                ]
            ]);

            $this->bus->close();
        }

        return $response;
    }

    public function getBus(): BusInterface
    {
        return $this->bus;
    }

    public function setBus(BusInterface $bus): void
    {
        $this->bus = $bus;
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

    public function __sleep()
    {
        $sanatizeDispatcher = function () {
            $this->sorted = [];
            $this->optimized = null;
        };

        if ($this->eventDispatcher instanceof EventDispatcher) {
            $sanatizeDispatcher->call($this->eventDispatcher);
        }

        if ($this->captureEventDispatcher instanceof EventDispatcher) {
            $sanatizeDispatcher->call($this->captureEventDispatcher);
        }

        $removeParent = function () {
            $this->parent = null;
        };

        foreach ($this->children() as $child) {
            $removeParent->call($child);

            $sleepChildEvent = new SleepChildEvent($child);
            $this->eventDispatcher->dispatch($sleepChildEvent);

            if ($child instanceof StratusComponentInterface) {
                $child->setApp(null);
            }

            $sanatizeDispatcher->call($child->getEventDispatcher());

            if ($child instanceof CompositeComponentInterface) {
                $sanatizeDispatcher->call($child->getCaptureEventDispatcher());
            }
        }

        $vars = get_object_vars($this);
        $nonSerializable = ['inmutableView'];

        $result = array_diff(array_keys($vars), $nonSerializable);

        return $result;
    }

    public function __wakeup()
    {
        $update = function ($child, $parent) use (&$update) {
            $child->setParent($parent);

            if ($child instanceof CompositeComponentInterface) {
                foreach ($child->getChilds() as $subchild) {
                    $update($subchild, $child);
                }
            }

            if ($child instanceof StratusComponentInterface) {
                $child->setApp($this);
            }
        };

        foreach ($this->getChilds() as $child) {
            $update($child, $this);
        }
    }

    public function executeFrontCall(FrontCall $frontCall, bool $queryMode = true)
    {
        $hash = $frontCall->getHash();
        $frontCalls = $this->currentRequest->getExecutedFrontCalls();

        if (array_key_exists($hash, $frontCalls)) {
            return $frontCalls[$hash];
        }

        if ($queryMode) {
            throw new FrontCallException($frontCall);
        } else {
            $this->bus->write([
                'frontCall' => [
                    'hash' => $frontCall->getHash(),
                    'script' => $frontCall->getScript(),
                ]
            ]);
        }
    }

    public function showAlert(string $text): void
    {
        $this->invokeJavaScriptFunction(JavaScriptUtils::class, 'alert', compact('text'));
    }

    public function redirect(string $url): void
    {
        $this->invokeJavaScriptFunction(JavaScriptUtils::class, 'redirect', compact('url'));
    }
}
