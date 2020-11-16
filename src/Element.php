<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP;

use ThenLabs\StratusPHP\Exception\InvokationBeforeBootException;
use ThenLabs\StratusPHP\Exception\MissingDataException;
use ThenLabs\Components\CompositeComponentInterface;
use ThenLabs\Components\CompositeComponentTrait;
use ThenLabs\StratusPHP\JavaScript\JavaScriptUtils;
use Wa72\HtmlPageDom\HtmlPageCrawler;
use TypeError;
use BadMethodCallException;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class Element implements CompositeComponentInterface, StratusComponentInterface, QuerySelectorInterface
{
    use CompositeComponentTrait;

    protected $selector;
    protected $properties = [];
    protected $criticalProperties = [];
    protected $crawler;
    protected $app;
    protected $jsVarName;

    public function __construct(string $selector)
    {
        $this->selector = $selector;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public static function getJavaScriptClassMembers(): string
    {
        return <<<JAVASCRIPT
            constructor(id, parentElement, selector) {
                this.id = id;
                this.parentElement = parentElement;
                this.selector = selector;
                this.element = parentElement.querySelector(selector);
                this.criticalProperties = [];
            }

            static createNew(classId, componentId, parent, selector) {
                const ComponentClass = stratusAppInstance.classes[classId];
                const component = new ComponentClass(
                    componentId,
                    stratusAppInstance.rootElement,
                    selector
                );

                stratusAppInstance.addComponent(component);
            }

            static setProperty(componentId, property, value) {
                const element = app.getComponent(componentId).element;

                if ('string' === typeof(value)) {
                    value = '`' + value + '`';
                }

                eval(`element.\${property} = \${value}`);
            }

            static setAttribute(componentId, attribute, value) {
                app.getComponent(componentId).element.setAttribute(attribute, value);
            }

            static removeAttribute(componentId, attribute) {
                app.getComponent(componentId).element.removeAttribute(attribute);
            }

            static addClass(componentId, className) {
                app.getComponent(componentId).element.classList.add(className);
            }

            static removeClass(componentId, className) {
                app.getComponent(componentId).element.classList.remove(className);
            }

            static remove(componentId) {
                app.getComponent(componentId).element.remove();

                delete app.components[componentId];
            }

            static append(componentId, html) {
                const newElement = document.createElement('DIV');
                newElement.innerHTML = html;

                const element = app.getComponent(componentId).element;
                element.append(newElement.firstElementChild);
            }

            static prepend(componentId, html) {
                const newElement = document.createElement('DIV');
                newElement.innerHTML = html;

                const element = app.getComponent(componentId).element;
                element.prepend(newElement.firstElementChild);
            }

            getCriticalData() {
                let result = {};

                for (let data of this.criticalProperties) {
                    result[data] = this.element[data];
                }

                return result;
            }

            registerCriticalProperty(property) {
                this.criticalProperties.push(property);
            }
        JAVASCRIPT;
    }

    public function getJavaScriptCreateInstanceScript(): string
    {
        $myId = $this->getId();

        $jsEvents = '';

        foreach ($this->getCaptureEventDispatcher()->getListeners() as $eventName => $listeners) {
            foreach ($listeners as $listener) {
                $jsEventData = '';
                $frontListenerSrc = '';

                if ($listener instanceof StratusEventListener) {
                    foreach ($listener->getFetchData() as $key) {
                        $jsEventData .= "eventData['{$key}'] = event['{$key}'];\n";
                    }

                    $frontListenerSrc = $listener->getFrontListener();
                }

                $jsEvents .= <<<JAVASCRIPT
                    component.element.addEventListener('{$eventName}', event => {
                        event.backListener = true;

                        let eventData = {
                            target: {
                                attributes: event.target.attributes,
                                innerHTML: event.target.innerHTML,
                            }
                        };

                        {$jsEventData}

                        {$frontListenerSrc}

                        if (event.backListener) {
                            app.dispatch('{$myId}.{$eventName}', eventData, true);
                        }
                    }, true);
                JAVASCRIPT;
            }
        }

        foreach ($this->getEventDispatcher()->getListeners() as $eventName => $listeners) {
            foreach ($listeners as $listener) {
                $jsEventData = '';
                $frontListenerSrc = '';

                if ($listener instanceof StratusEventListener) {
                    foreach ($listener->getFetchData() as $key) {
                        $jsEventData .= "eventData['{$key}'] = event['{$key}'];\n";
                    }

                    $frontListenerSrc = $listener->getFrontListener();
                }

                $jsEvents .= <<<JAVASCRIPT
                    component.element.addEventListener('{$eventName}', event => {
                        event.backListener = true;

                        let eventData = {};
                        {$jsEventData}

                        {$frontListenerSrc}

                        if (event.backListener) {
                            app.dispatch('{$myId}.{$eventName}', eventData);
                        }
                    });
                JAVASCRIPT;
            }
        }

        $parent = $this->getParent();
        $jsParentElement = $parent instanceof AbstractApp ?
            'document' :
            "app.getComponent('{$parent->getId()}').element"
        ;

        $jsRegisterCriticalProperties = '';
        foreach ($this->criticalProperties as $property) {
            $jsRegisterCriticalProperties .= "component.registerCriticalProperty('{$property}');\n";
        }

        $jsVarName = $this->jsVarName ? "var {$this->jsVarName} = component.element;" : null;

        $jsClassId = $this->app->getJavaScriptClassId(self::class);

        return <<<JAVASCRIPT
            const parentElement = {$jsParentElement};
            const StratusElementClass = app.getClass('{$jsClassId}');
            const component = new StratusElementClass('{$myId}', parentElement, '{$this->selector}');
            app.addComponent(component);
            {$jsVarName}
            {$jsRegisterCriticalProperties}
            {$jsEvents}
        JAVASCRIPT;
    }

    public function __call(string $methodName, array $arguments): void
    {
        $matches = [];
        preg_match('/on([a-zA-Z][a-zA-Z0-9]+)/', $methodName, $matches);

        $exceptionMessage = "Bad method call for '{$methodName}'.";

        if (! empty($matches)) {
            $listener = $arguments[0];

            if (! is_callable($listener)) {
                throw new BadMethodCallException($exceptionMessage);
            }

            $eventName = strtolower($matches[1]);

            $this->on($eventName, $listener);
            return;
        }

        throw new BadMethodCallException($exceptionMessage);
    }

    public function setAttribute(string $attribute, $value): void
    {
        $this->verifyAppBooted(__METHOD__);

        $data = [
            'componentId' => $this->getId(),
            'attribute' => $attribute,
            'value' => $value,
        ];

        $this->app->invokeJavaScriptFunction(self::class, 'setAttribute', $data);

        if (! isset($this->properties['attributes'])) {
            $this->properties['attributes'] = [];
        }

        $this->properties['attributes'][$attribute] = $value;
    }

    public function getAttribute(string $attribute)
    {
        $this->verifyAppBooted(__METHOD__);

        return $this->attributes[$attribute];
    }

    public function hasAttribute(string $attribute): bool
    {
        $this->verifyAppBooted(__METHOD__);

        return isset($this->attributes[$attribute]);
    }

    public function removeAttribute(string $attribute): void
    {
        $this->verifyAppBooted(__METHOD__);

        $this->app->invokeJavaScriptFunction(self::class, 'removeAttribute', [
            'componentId' => $this->getId(),
            'attribute' => $attribute,
        ]);
    }

    public function hasClass(string $cssClass): bool
    {
        $this->verifyAppBooted(__METHOD__);

        return in_array($cssClass, $this->classList);
    }

    public function addClass(string $cssClass): void
    {
        $this->verifyAppBooted(__METHOD__);

        $this->app->invokeJavaScriptFunction(self::class, 'addClass', [
            'componentId' => $this->getId(),
            'cssClass' => $cssClass,
        ]);
    }

    public function removeClass(string $cssClass): void
    {
        $this->verifyAppBooted(__METHOD__);

        $this->app->invokeJavaScriptFunction(self::class, 'removeClass', [
            'componentId' => $this->getId(),
            'cssClass' => $cssClass,
        ]);
    }

    public function setStyle(string $property, string $value): void
    {
        $this->verifyAppBooted(__METHOD__);

        $this->setPropertyOnFront("style.{$property}", $value);
    }

    public function getStyle(string $property): string
    {
        $this->verifyAppBooted(__METHOD__);

        return $this->style[$property];
    }

    public function getCrawler(): ?HtmlPageCrawler
    {
        return $this->crawler;
    }

    public function setCrawler(?HtmlPageCrawler $crawler): void
    {
        $this->crawler = $crawler;
    }

    public function getSelector(): string
    {
        return $this->selector;
    }

    public function querySelector(string $selector): self
    {
        foreach ($this->childs as $component) {
            if ($component instanceof self &&
                $component->getSelector() == $selector
            ) {
                return $component;
            }
        }

        if (! $this->app || ! $this->app->isBooted()) {
            $element = new self($selector);
            $element->setCrawler($this->crawler->filter($selector));

            if ($this->app instanceof AbstractApp) {
                $element->setApp($this->app);
            }

            $this->addChild($element);

            return $element;
        } else {
            return $this->parent->querySelector("{$this->selector} > {$selector}");
        }
    }

    public function setApp(?AbstractApp $app): void
    {
        $this->app = $app;
    }

    public function getApp(): ?AbstractApp
    {
        return $this->app;
    }

    public function __get($name)
    {
        if (! isset($this->properties[$name])) {
            throw new MissingDataException(<<<JAVASCRIPT
                const component = stratusAppInstance.getComponent('{$this->getId()}');

                component.registerCriticalProperty('{$name}');

                return {
                    componentData: {
                        '{$this->getId()}': {
                            '{$name}': component.element['{$name}']
                        }
                    }
                };
            JAVASCRIPT);
        }

        return $this->properties[$name];
    }

    public function __set($name, $value)
    {
        $this->setPropertyOnFront($name, $value);

        $this->properties[$name] = $value;
    }

    public function __isset($name)
    {
        return true;
    }

    public function updateData(string $key, $value): void
    {
        $this->properties[$key] = $value;
    }

    private function setPropertyOnFront(string $property, $value): void
    {
        $data = [
            'componentId' => $this->getId(),
            'property' => $property,
            'value' => is_string($value) ? $value : var_export($value, true),
        ];

        $this->app->invokeJavaScriptFunction(self::class, 'setProperty', $data);
    }

    private function verifyAppBooted(string $method): void
    {
        if (! $this->app->isBooted()) {
            throw new InvokationBeforeBootException($method);
        }
    }

    public function registerCriticalProperty(string $property): void
    {
        $this->criticalProperties[] = $property;
    }

    public function addEventListener(string $eventName, $listener, bool $capture = false): void
    {
        if (is_callable($listener)) {
            $this->on($eventName, $listener, $capture);
            return;
        }

        if (is_array($listener)) {
            $stratusEventListener = new StratusEventListener($listener);
            $this->on($eventName, $stratusEventListener, $capture);
            return;
        }

        throw new TypeError('Invalid listener.');
    }

    public function remove(): void
    {
        $this->setParent(null);

        $this->app->invokeJavaScriptFunction(self::class, 'remove', [
            'componentId' => $this->getId(),
        ]);
    }

    public function getProperties(): array
    {
        return $this->properties;
    }

    public function setProperties(array $properties): void
    {
        $this->properties = $properties;
    }

    public function setJsVarName(string $jsVarName): void
    {
        $this->jsVarName = $jsVarName;
    }

    public static function createFromString(string $html): self
    {
       $crawler = new HtmlPageCrawler($html);

        $element = new self('');
        $element->setCrawler($crawler);

        return $element;
    }

    public function append(self $child, string $mode = 'append'): void
    {
        $this->verifyAppBooted(__METHOD__);

        if (! $child->getSelector()) {
            $child->selector = $this->selector.' > '.$child->getCrawler()->getNode(0)->tagName;
        }

        $this->addChild($child);

        $this->app->invokeJavaScriptFunction(self::class, $mode, [
            'componentId' => $this->getId(),
            'html' => (string) $child,
        ]);

        $this->app->invokeJavaScriptFunction(JavaScriptUtils::class, 'eval', [
            'code' => $child->getJavaScriptCreateInstanceScript()
        ]);
    }

    public function prepend(self $child): void
    {
        $this->append($child, 'prepend');
    }

    public function __toString()
    {
        return $this->crawler->saveHTML();
    }
}
