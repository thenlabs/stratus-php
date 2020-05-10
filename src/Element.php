<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP;

use ThenLabs\StratusPHP\JavaScript\JavaScriptInstanceInterface;
use ThenLabs\Components\CompositeComponentInterface;
use ThenLabs\Components\CompositeComponentTrait;
use Wa72\HtmlPageDom\HtmlPageCrawler;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class Element implements CompositeComponentInterface, JavaScriptInstanceInterface, QuerySelectorInterface
{
    use CompositeComponentTrait;
    use SleepTrait;

    protected $cssSelector;
    protected $attributes = [];
    protected $properties = [];
    protected $crawler;
    protected $app;

    public function __construct(string $cssSelector)
    {
        $this->cssSelector = $cssSelector;
    }

    public static function getJavaScriptClassMembers(): string
    {
        return <<<JAVASCRIPT
            constructor(id, parentElement, selector) {
                this.id = id;
                this.parentElement = parentElement;
                this.selector = selector;
                this.element = parentElement.querySelector(selector);
            }

            static setProperty(messageData) {
                const component = app.getComponent(messageData.componentId);
                component.element[messageData.property] = messageData.value;
            }

            static createNew(messageData) {
                const ComponentClass = stratusAppInstance.classes[messageData.classId];
                const component = new ComponentClass(
                    messageData.componentId,
                    stratusAppInstance.rootElement,
                    messageData.selector
                );

                stratusAppInstance.addComponent(component);
            }

            static setStyle(messageData) {
                const component = app.getComponent(messageData.componentId);
                component.element.style[messageData.property] = messageData.value;
            }
        JAVASCRIPT;
    }

    public function getJavaScriptCreateInstanceScript(): string
    {
        $myId = $this->getId();

        $jsAttributes = '';
        $jsEvents = '';

        foreach ($this->attributes as $attribute => $value) {
            $jsAttribute = var_export($attribute, true);
            $jsValue = var_export($value, true);

            $jsAttributes .= <<<JAVASCRIPT
                component.element.setAttribute({$jsAttribute}, {$jsValue});\n
            JAVASCRIPT;
        }

        foreach ($this->eventDispatcher->getListeners() as $eventName => $listeners) {
            $jsEvents .= <<<JAVASCRIPT
                component.element.addEventListener('{$eventName}', () => {
                    app.dispatch('{$myId}.{$eventName}');
                });
            JAVASCRIPT;
        }

        $parent = $this->getParent();
        $jsParentElement = $parent instanceof AbstractApp ?
            'document' :
            "app.getComponent('{$parent->getId()}').element"
        ;

        return <<<JAVASCRIPT
            const parentElement = {$jsParentElement};
            const component = new ComponentClass('{$myId}', parentElement, '{$this->cssSelector}');
            app.addComponent(component);
            {$jsAttributes}
            {$jsEvents}
        JAVASCRIPT;
    }

    public function click(callable $listener): void
    {
        $this->on('click', $listener);
    }

    public function setAttribute(string $attribute, $value): void
    {
        $this->attributes[$attribute] = $value;
        $this->crawler->setAttribute($attribute, $value);
    }

    public function getAttribute(string $attribute)
    {
        return $this->crawler->getAttribute($attribute);
    }

    public function hasClass(string $cssClass): bool
    {
        return $this->crawler->hasClass($cssClass);
    }

    public function addClass(string $cssClass): void
    {
        $this->crawler->addClass($cssClass);
    }

    public function getCrawler(): ?HtmlPageCrawler
    {
        return $this->crawler;
    }

    public function setCrawler(HtmlPageCrawler $crawler): void
    {
        $this->crawler = $crawler;
    }

    public function getCssSelector(): string
    {
        return $this->cssSelector;
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

        $element = new Element($cssSelector);
        $element->setCrawler($this->crawler->filter($cssSelector));

        if ($this->app instanceof AbstractApp) {
            $element->setApp($this->app);
        }

        $this->addChild($element);

        return $element;
    }

    public function setApp(AbstractApp $app): void
    {
        $this->app = $app;
    }

    public function getApp(): AbstractApp
    {
        return $this->app;
    }

    public function __get($name)
    {
        return $this->properties[$name] ?? null;
    }

    public function __set($name, $value)
    {
        $this->app->getBus()->write([
            'handler' => [
                'classId' => $this->app->getJavaScriptClassId(self::class),
                'method' => 'setProperty'
            ],
            'data' => [
                'componentId' => $this->getId(),
                'property' => $name,
                'value' => $value,
            ],
        ]);

        $this->properties[$name] = $value;
    }

    public function css(string $property, string $value): void
    {
        $this->app->getBus()->write([
            'handler' => [
                'classId' => $this->app->getJavaScriptClassId(self::class),
                'method' => 'setStyle'
            ],
            'data' => [
                'componentId' => $this->getId(),
                'property' => $property,
                'value' => $value,
            ],
        ]);
    }
}
