<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Plugin\PageDom;

use ThenLabs\StratusPHP\AbstractPage;
use ThenLabs\StratusPHP\Exception\InvokationBeforeBootException;
use ThenLabs\StratusPHP\Event\EventListener;
use ThenLabs\StratusPHP\Component\ComponentInterface as StratusComponentInterface;
use ThenLabs\Components\CompositeComponentInterface;
use ThenLabs\Components\CompositeComponentTrait;
use Wa72\HtmlPageDom\HtmlPageCrawler;
use TypeError;
use BadMethodCallException;

/**
 * Represents an Element of the page DOM.
 *
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class Element implements CompositeComponentInterface, StratusComponentInterface
{
    use CompositeComponentTrait;

    /**
     * @var string
     */
    protected $selector;

    /**
     * @var array
     */
    protected $properties = [];

    /**
     * @var array
     */
    protected $criticalProperties = [];

    /**
     * @var HtmlPageCrawler|null
     */
    protected $crawler;

    /**
     * @var AbstractPage
     */
    protected $page;

    /**
     * @var string
     */
    protected $jsVarName;

    /**
     * @param string $selector
     */
    public function __construct(string $selector)
    {
        $this->selector = $selector;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
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
        $jsParentElement = $parent instanceof AbstractPage ?
            'document' :
            "app.getComponent('{$parent->getId()}').element"
        ;

        $jsRegisterCriticalProperties = '';
        foreach ($this->criticalProperties as $property) {
            $jsRegisterCriticalProperties .= "component.registerCriticalProperty('{$property}');\n";
        }

        $jsVarName = $this->jsVarName ? "var {$this->jsVarName} = component.element;" : null;

        $jsClassId = $this->page->getJavaScriptClassId(self::class);

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

    /**
     * Sets an attribute to the HTML element.
     *
     * @param string $attribute
     * @param mixed  $value
     */
    public function setAttribute(string $attribute, $value): void
    {
        $this->verifyAppBooted(__METHOD__);

        $jsValue = var_export($value, true);

        $this->page->executeScript(<<<JAVASCRIPT
            let component = stratusAppInstance.getComponent('{$this->getId()}');
            component.element.setAttribute('{$attribute}', {$jsValue});
        JAVASCRIPT, false);

        if (! isset($this->properties['attributes'])) {
            $this->properties['attributes'] = [];
        }

        $this->properties['attributes'][$attribute] = $value;
    }

    /**
     * Returns an attribute of the HTML element.
     *
     * @param  string $attribute
     * @return mixed
     */
    public function getAttribute(string $attribute)
    {
        $this->verifyAppBooted(__METHOD__);

        return $this->attributes[$attribute];
    }

    /**
     * Checks if the HTML element has an attribute.
     *
     * @param  string  $attribute
     * @return boolean
     */
    public function hasAttribute(string $attribute): bool
    {
        $this->verifyAppBooted(__METHOD__);

        return isset($this->attributes[$attribute]);
    }

    /**
     * Remove an attribute of the HTML element.
     *
     * @param string $attribute
     */
    public function removeAttribute(string $attribute): void
    {
        $this->verifyAppBooted(__METHOD__);

        $this->page->executeScript(<<<JAVASCRIPT
            let component = stratusAppInstance.getComponent('{$this->getId()}');
            component.element.removeAttribute('{$attribute}');
        JAVASCRIPT, false);
    }

    /**
     * Checks if the HTML element has assigned a css class.
     *
     * @param  string  $cssClass
     * @return boolean
     */
    public function hasClass(string $cssClass): bool
    {
        $this->verifyAppBooted(__METHOD__);

        return in_array($cssClass, $this->classList);
    }

    /**
     * Adds a css class to the HTML element.
     *
     * @param string $cssClass
     */
    public function addClass(string $cssClass): void
    {
        $this->verifyAppBooted(__METHOD__);

        $this->page->executeScript(<<<JAVASCRIPT
            let component = stratusAppInstance.getComponent('{$this->getId()}');
            component.element.classList.add('{$cssClass}');
        JAVASCRIPT, false);
    }

    /**
     * Remove a css class of the HTML element.
     *
     * @param  string $cssClass
     */
    public function removeClass(string $cssClass): void
    {
        $this->verifyAppBooted(__METHOD__);

        $this->page->executeScript(<<<JAVASCRIPT
            let component = stratusAppInstance.getComponent('{$this->getId()}');
            component.element.classList.remove('{$cssClass}');
        JAVASCRIPT, false);
    }

    /**
     * Sets a css property on the HTML element.
     *
     * @param string $property
     * @param string $value
     */
    public function setStyle(string $property, string $value): void
    {
        $this->verifyAppBooted(__METHOD__);

        $jsValue = var_export($value, true);

        $this->page->executeScript(<<<JAVASCRIPT
            let component = stratusAppInstance.getComponent('{$this->getId()}');
            component.element.style['{$property}'] = {$jsValue};
        JAVASCRIPT, false);
    }

    /**
     * Returns a css property of the HTML element.
     *
     * @param  string $property
     * @return string
     */
    public function getStyle(string $property): string
    {
        $this->verifyAppBooted(__METHOD__);

        return $this->style[$property];
    }

    /**
     * @return HtmlPageCrawler|null
     */
    public function getCrawler(): ?HtmlPageCrawler
    {
        return $this->crawler;
    }

    /**
     * @param HtmlPageCrawler|null $crawler
     */
    public function setCrawler(?HtmlPageCrawler $crawler): void
    {
        $this->crawler = $crawler;
    }

    /**
     * @return string
     */
    public function getSelector(): string
    {
        return $this->selector;
    }

    /**
     * Returns a child element.
     *
     * @param  string $selector the css selector.
     * @return self|null
     */
    public function querySelector(string $selector): self
    {
        foreach ($this->childs as $component) {
            if ($component instanceof self &&
                $component->getSelector() == $selector
            ) {
                return $component;
            }
        }

        if (! $this->page || ! $this->page->isBooted()) {
            $element = new self($selector);
            $element->setCrawler($this->crawler->filter($selector));

            if ($this->page instanceof AbstractPage) {
                $element->setPage($this->page);
            }

            $this->addChild($element);

            return $element;
        } else {
            return $this->parent->querySelector("{$this->selector} > {$selector}");
        }
    }

    /**
     * @param AbstractPage|null $page
     */
    public function setPage(?AbstractPage $page): void
    {
        $this->page = $page;
    }

    /**
     * @return AbstractPage|null
     */
    public function getPage(): ?AbstractPage
    {
        return $this->page;
    }

    /**
     * Returns a property of the HTML element.
     */
    public function __get($name)
    {
        if (! array_key_exists($name, $this->properties)) {
            $this->page->executeScript(<<<JAVASCRIPT
                const component = stratusAppInstance.getComponent('{$this->getId()}');
                component.registerCriticalProperty('{$name}');
            JAVASCRIPT, true);
        }

        return $this->properties[$name];
    }

    /**
     * Sets a property on the HTML element.
     */
    public function __set($name, $value)
    {
        $jsValue = is_string($value) ? "`{$value}`" : var_export($value, true);

        $this->page->executeScript(<<<JAVASCRIPT
            let component = stratusAppInstance.getComponent('{$this->getId()}');
            component.element['{$name}'] = {$jsValue};
        JAVASCRIPT, false);

        $this->properties[$name] = $value;
    }

    public function __isset($name)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function updateData(string $key, $value): void
    {
        $this->properties[$key] = $value;
    }

    private function verifyAppBooted(string $method): void
    {
        if (! $this->page->isBooted()) {
            throw new InvokationBeforeBootException($method);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function registerCriticalData(string $dataName): void
    {
        $this->criticalProperties[] = $dataName;
    }

    /**
     * @param string   $eventName
     * @param callable $listener
     * @param boolean  $capture
     */
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

    /**
     * Remove the HTML element and this component.
     */
    public function remove(): void
    {
        $this->setParent(null);

        $this->page->executeScript(<<<JAVASCRIPT
            let component = stratusAppInstance.getComponent('{$this->getId()}');
            component.element.remove();
            delete stratusAppInstance.components[component.id];
        JAVASCRIPT, false);
    }

    /**
     * @return array
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * @param array $properties
     */
    public function setProperties(array $properties): void
    {
        $this->properties = $properties;
    }

    /**
     * @param string $jsVarName
     */
    public function setJsVarName(string $jsVarName): void
    {
        $this->jsVarName = $jsVarName;
    }

    /**
     * @param  string $html
     * @return self
     */
    public static function createFromString(string $html): self
    {
        $crawler = new HtmlPageCrawler($html);

        $element = new self('');
        $element->setCrawler($crawler);

        return $element;
    }

    /**
     * Append a child on the HTML element.
     *
     * @param  self   $child
     * @param  string $mode  The mode may be 'append' or 'prepend'.
     */
    public function append(self $child, string $mode = 'append'): void
    {
        $this->verifyAppBooted(__METHOD__);

        if (! $child->getSelector()) {
            $child->selector = $this->selector.' > '.$child->getCrawler()->getNode(0)->tagName;
        }

        $this->addChild($child);

        $html = (string) $child;

        $this->page->executeScript(<<<JAVASCRIPT
            const newElement = document.createElement('DIV');
            newElement.innerHTML = `{$html}`;

            const element = stratusAppInstance.getComponent('{$this->getId()}').element;
            element.{$mode}(newElement.firstElementChild);
        JAVASCRIPT, false);
    }

    /**
     * Prepend a child on the HTML element.
     *
     * @param  self $child
     */
    public function prepend(self $child): void
    {
        $this->append($child, 'prepend');
    }

    public function __toString()
    {
        return $this->crawler->saveHTML();
    }
}
