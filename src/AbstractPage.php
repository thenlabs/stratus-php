<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP;

use ThenLabs\Components\ComponentInterface;
use ThenLabs\Components\CompositeComponentInterface;
use ThenLabs\Components\Event\BeforeInsertionEvent;
use ThenLabs\Components\Event\FilterDependenciesEvent;
use ThenLabs\ComposedViews\AbstractCompositeView;
use ThenLabs\ComposedViews\Event\RenderEvent;
use ThenLabs\ComposedViews\Asset\Script;
use ThenLabs\StratusPHP\Annotation\OnConstructor;
use ThenLabs\StratusPHP\Annotation\Sleep;
use ThenLabs\StratusPHP\Asset\StratusScript;
use ThenLabs\StratusPHP\Asset\StratusInitScript;
use ThenLabs\StratusPHP\Component\ComponentInterface as StratusComponentInterface;
use ThenLabs\StratusPHP\Component\Browser;
use ThenLabs\StratusPHP\Event\Event;
use ThenLabs\StratusPHP\Event\SleepChildEvent;
use ThenLabs\StratusPHP\Exception\InmutableViewException;
use ThenLabs\StratusPHP\Exception\InvalidTokenException;
use ThenLabs\StratusPHP\Exception\FrontCallException;
use ThenLabs\StratusPHP\Bus\BusInterface;
use ThenLabs\StratusPHP\Bus\StreamingBus;
use ThenLabs\StratusPHP\JavaScript\JavaScriptClassInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\AnnotationReader;
use ReflectionClass;

AnnotationRegistry::registerFile(__DIR__.'/Annotation/EventListener.php');
AnnotationRegistry::registerFile(__DIR__.'/Annotation/OnConstructor.php');
AnnotationRegistry::registerFile(__DIR__.'/Annotation/Sleep.php');

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 * @abstract
 */
abstract class AbstractPage extends AbstractCompositeView
{
    /**
     * @var string
     */
    protected $ajaxControllerUri;

    /**
     * @var array
     */
    protected $javaScriptClasses = [];

    /**
     * @var array
     */
    protected $classListWithTotalInsertionCapability = [];

    /**
     * @var boolean
     */
    protected $debug = false;

    /**
     * @var boolean
     */
    protected $booted = false;

    /**
     * @var BusInterface
     */
    protected $bus;

    /**
     * @Sleep
     * @var string
     */
    protected $inmutableView;

    /**
     * @var string
     */
    protected $token;

    /**
     * @var Request
     */
    protected $currentRequest;

    /**
     * @var Browser
     */
    protected $browser;

    /**
     * Uri of the processing controller.
     *
     * @param string $ajaxControllerUri
     */
    public function __construct(string $ajaxControllerUri, bool $runPlugins = true)
    {
        parent::__construct();

        $this->ajaxControllerUri = $ajaxControllerUri;
        $this->bus = new StreamingBus;
        $this->token = uniqid('token', true);

        $this->browser = new Browser;
        $this->browser->setPage($this);

        $this->on(BeforeInsertionEvent::class, [$this, '_beforeInsertionEvent']);
        $this->on(
            FilterDependenciesEvent::class."_{$this->getId()}",
            [$this, '_filterDependenciesEvent']
        );

        $this->addFilter([$this, '_insertAssets']);

        if ($runPlugins) {
            $this->runPlugins();
        }
    }

    /**
     * @return Browser
     */
    public function getBrowser(): Browser
    {
        return $this->browser;
    }

    /**
     * @return array
     */
    public function getOwnDependencies(): array
    {
        $stratusScript = new StratusScript('stratus', null, '');
        $stratusScript->setPage($this);
        $stratusScript->setAttribute('class', 'stratus');

        $stratusInitScript = new StratusInitScript('stratus-init', null, '');
        $stratusInitScript->setPage($this);
        $stratusInitScript->setAttribute('class', 'stratus-init');

        return compact('stratusScript', 'stratusInitScript');
    }

    /**
     * @param  array   $data
     * @param  boolean $dispatchRenderEvent
     * @return string
     */
    public function render(array $data = [], bool $dispatchRenderEvent = true): string
    {
        $this->updateJavaScriptClasses();

        return parent::render($data, $dispatchRenderEvent);
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @return string
     */
    public function getAjaxControllerUri(): string
    {
        return $this->ajaxControllerUri;
    }

    /**
     * @param string $ajaxControllerUri
     */
    public function setAjaxControllerUri(string $ajaxControllerUri): void
    {
        $this->ajaxControllerUri = $ajaxControllerUri;
    }

    /**
     * @param  string $className
     * @return string
     */
    public function registerJavaScriptClass(string $className): string
    {
        $classId = $this->debug ? $className : uniqid('Class');

        $this->javaScriptClasses[$className] = $classId;

        return $classId;
    }

    /**
     * @param  string      $className
     * @return string|null
     */
    public function getJavaScriptClassId(string $className): ?string
    {
        return $this->javaScriptClasses[$className] ?? null;
    }

    /**
     * @return boolean
     */
    public function isDebug(): bool
    {
        return $this->debug;
    }

    /**
     * @param boolean $debug
     */
    public function setDebug(bool $debug): void
    {
        $this->debug = $debug;
    }

    /**
     * @return boolean
     */
    public function isBooted(): bool
    {
        return $this->booted;
    }

    /**
     * @param boolean $booted
     */
    public function setBooted(bool $booted): void
    {
        $this->booted = $booted;
    }

    /**
     * @return array
     */
    public function getJavaScriptClasses(): array
    {
        return $this->javaScriptClasses;
    }

    /**
     * @param array $javaScriptClasses
     */
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

    /**
     * @param RenderEvent $event
     */
    public function _insertAssets(RenderEvent $event): void
    {
        $body = $event->filter('body');
        $dependencies = $this->getDependencies();

        foreach ($dependencies as $dependencyName => $dependency) {
            if ($dependency instanceof Script) {
                $dependency->setAttribute('class', $dependencyName);
                $element = $body->filter("script.{$dependencyName}");

                if (0 === count($element)) {
                    $body->append($dependency->render());
                }
            }
        }
    }

    /**
     * @param  BeforeInsertionEvent $event
     */
    public function _beforeInsertionEvent(BeforeInsertionEvent $event): void
    {
        $child = $event->getChild();

        if ($this->hasInmutableView() &&
            ! in_array(get_class($child), $this->classListWithTotalInsertionCapability)
        ) {
            throw new InmutableViewException;
        }

        if ($child instanceof StratusComponentInterface) {
            $child->setPage($this);
        }
    }

    /**
     * The assets stratus and stratus-init should be the lastest.
     *
     * @param  FilterDependenciesEvent $event
     */
    public function _filterDependenciesEvent(FilterDependenciesEvent $event): void
    {
        $dependencies = $event->getDependencies();

        $stratusScript = $dependencies['stratus'];
        $stratusInitScript = $dependencies['stratus-init'];

        unset($dependencies['stratus']);
        unset($dependencies['stratus-init']);

        $dependencies['stratus'] = $stratusScript;
        $dependencies['stratus-init'] = $stratusInitScript;

        $event->setDependencies($dependencies);
    }

    /**
     * @param  ComponentInterface $child
     * @return boolean
     */
    public function validateChild(ComponentInterface $child): bool
    {
        return true;
    }

    /**
     * @return boolean
     */
    public function hasInmutableView(): bool
    {
        return $this->inmutableView ? true : false;
    }

    /**
     * @param callable $callback
     */
    public function addFilter(callable $callback): void
    {
        if ($this->hasInmutableView()) {
            throw new InmutableViewException;
        }

        parent::addFilter($callback);
    }

    /**
     * @param  Request $request
     * @return Response
     */
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
        $event->setPage($this);
        $event->setSource($component);
        $event->setEventData($eventData = $request->getEventData());

        try {
            if ($request->isCapture()) {
                $component->getCaptureEventDispatcher()->dispatch($event, $eventName);
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

    /**
     * @return BusInterface
     */
    public function getBus(): BusInterface
    {
        return $this->bus;
    }

    /**
     * @param BusInterface $bus
     */
    public function setBus(BusInterface $bus): void
    {
        $this->bus = $bus;
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
                $child->setPage(null);
            }

            $sanatizeDispatcher->call($child->getEventDispatcher());

            if ($child instanceof CompositeComponentInterface) {
                $sanatizeDispatcher->call($child->getCaptureEventDispatcher());
            }
        }

        $vars = get_object_vars($this);
        $nonSerializable = [];

        $class = new ReflectionClass($this);
        $annotationReader = new AnnotationReader;

        foreach ($class->getProperties() as $property) {
            if ($annotation = $annotationReader->getPropertyAnnotation($property, Sleep::class)) {
                $nonSerializable[] = $property->getName();
            }
        }

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
                $child->setPage($this);
            }
        };

        foreach ($this->getChilds() as $child) {
            $update($child, $this);
        }
    }

    /**
     * @param  FrontCall $frontCall
     * @return mixed
     */
    private function executeFrontCall(FrontCall $frontCall)
    {
        $hash = $frontCall->getHash();
        $frontCalls = $this->currentRequest->getExecutedFrontCalls();

        if (array_key_exists($hash, $frontCalls)) {
            return $frontCalls[$hash];
        }

        if ($frontCall->getQueryMode()) {
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

    /**
     * @param  string $script
     * @param  bool   $queryMode
     * @return mixed
     */
    public function executeScript(string $script, bool $queryMode)
    {
        $frontCall = new FrontCall($script, $queryMode);

        return $this->executeFrontCall($frontCall);
    }

    public function runPlugins(): void
    {
        $class = new ReflectionClass($this);
        $annotationReader = new AnnotationReader;

        foreach ($class->getMethods() as $method) {
            if ($annotation = $annotationReader->getMethodAnnotation($method, OnConstructor::class)) {
                call_user_func([$this, $method->getName()]);
            }
        }
    }
}
