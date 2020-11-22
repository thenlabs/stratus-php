<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Plugin\PageDom;

use ThenLabs\StratusPHP\AbstractApp;
use ThenLabs\StratusPHP\FrontCall;
use ThenLabs\StratusPHP\Exception\InvokationBeforeBootException;
use ThenLabs\StratusPHP\Event\EventListener;
use ThenLabs\StratusPHP\Component\ComponentInterface as StratusComponentInterface;
use ThenLabs\Components\CompositeComponentInterface;
use ThenLabs\Components\CompositeComponentTrait;
use Wa72\HtmlPageDom\HtmlPageCrawler;
use TypeError;
use BadMethodCallException;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class Element implements CompositeComponentInterface, StratusComponentInterface
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

                if ($listener instanceof EventListener) {
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

                if ($listener instanceof EventListener) {
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

        $jsValue = var_export($value, true);

        $this->app->executeFrontCall(new FrontCall(<<<JAVASCRIPT
            let component = stratusAppInstance.getComponent('{$this->getId()}');
            component.element.setAttribute('{$attribute}', {$jsValue});
        JAVASCRIPT, false));

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

        $this->app->executeFrontCall(new FrontCall(<<<JAVASCRIPT
            let component = stratusAppInstance.getComponent('{$this->getId()}');
            component.element.removeAttribute('{$attribute}');
        JAVASCRIPT, false));
    }

    public function hasClass(string $cssClass): bool
    {
        $this->verifyAppBooted(__METHOD__);

        return in_array($cssClass, $this->classList);
    }

    public function addClass(string $cssClass): void
    {
        $this->verifyAppBooted(__METHOD__);

        $this->app->executeFrontCall(new FrontCall(<<<JAVASCRIPT
            let component = stratusAppInstance.getComponent('{$this->getId()}');
            component.element.classList.add('{$cssClass}');
        JAVASCRIPT, false));
    }

    public function removeClass(string $cssClass): void
    {
        $this->verifyAppBooted(__METHOD__);

        $this->app->executeFrontCall(new FrontCall(<<<JAVASCRIPT
            let component = stratusAppInstance.getComponent('{$this->getId()}');
            component.element.classList.remove('{$cssClass}');
        JAVASCRIPT, false));
    }

    public function setStyle(string $property, string $value): void
    {
        $this->verifyAppBooted(__METHOD__);

        $jsValue = var_export($value, true);

        $this->app->executeFrontCall(new FrontCall(<<<JAVASCRIPT
            let component = stratusAppInstance.getComponent('{$this->getId()}');
            component.element.style['{$property}'] = {$jsValue};
        JAVASCRIPT, false));
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
        if (! array_key_exists($name, $this->properties)) {
            $this->app->executeFrontCall(new FrontCall(<<<JAVASCRIPT
                const component = stratusAppInstance.getComponent('{$this->getId()}');
                component.registerCriticalProperty('{$name}');
            JAVASCRIPT, true));
        }

        return $this->properties[$name];
    }

    public function __set($name, $value)
    {
        $jsValue = is_string($value) ? "`{$value}`" : var_export($value, true);

        $this->app->executeFrontCall(new FrontCall(<<<JAVASCRIPT
            let component = stratusAppInstance.getComponent('{$this->getId()}');
            component.element['{$name}'] = {$jsValue};
        JAVASCRIPT, false));

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

    private function verifyAppBooted(string $method): void
    {
        if (! $this->app->isBooted()) {
            throw new InvokationBeforeBootException($method);
        }
    }

    public function registerCriticalData(string $dataName): void
    {
        $this->criticalProperties[] = $dataName;
    }

    public function addEventListener(string $eventName, $listener, bool $capture = false): void
    {
        if (is_callable($listener)) {
            $this->on($eventName, $listener, $capture);
            return;
        }

        if (is_array($listener)) {
            $stratusEventListener = new EventListener($listener);
            $this->on($eventName, $stratusEventListener, $capture);
            return;
        }

        throw new TypeError('Invalid listener.');
    }

    public function remove(): void
    {
        $this->setParent(null);

        $this->app->executeFrontCall(new FrontCall(<<<JAVASCRIPT
            let component = stratusAppInstance.getComponent('{$this->getId()}');
            component.element.remove();
            delete stratusAppInstance.components[component.id];
        JAVASCRIPT, false));
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

        $html = (string) $child;

        $this->app->executeFrontCall(new FrontCall(<<<JAVASCRIPT
            const newElement = document.createElement('DIV');
            newElement.innerHTML = `{$html}`;

            const element = stratusAppInstance.getComponent('{$this->getId()}').element;
            element.{$mode}(newElement.firstElementChild);
        JAVASCRIPT, false));
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
