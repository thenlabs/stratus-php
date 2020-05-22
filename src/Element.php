<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP;

use ThenLabs\StratusPHP\JavaScript\JavaScriptInstanceInterface;
use ThenLabs\StratusPHP\Exception\MissingComponentDataException;
use ThenLabs\Components\CompositeComponentInterface;
use ThenLabs\Components\CompositeComponentTrait;
use Wa72\HtmlPageDom\HtmlPageCrawler;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class Element implements CompositeComponentInterface, ComponentInterface, QuerySelectorInterface
{
    use CompositeComponentTrait;
    use SleepTrait;

    protected $selector;
    protected $attributes = [];
    protected $properties = [];
    protected $crawler;
    protected $app;
    private $nonSerializableProperties = ['crawler'];

    public function __construct(string $selector)
    {
        $this->selector = $selector;
    }

    public static function getJavaScriptClassMembers(): string
    {
        return <<<JAVASCRIPT
            constructor(id, parentElement, selector) {
                this.id = id;
                this.parentElement = parentElement;
                this.selector = selector;
                this.element = parentElement.querySelector(selector);
                this.criticalDataList = [];
            }

            static setProperty(componentId, property, value) {
                const component = app.getComponent(componentId);
                component.element[property] = value;
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

            static setStyle(componentId, property, value) {
                const component = app.getComponent(componentId);
                component.element.style[property] = value;
            }

            getCriticalData() {
                let result = {};

                for (let data of this.criticalDataList) {
                    result[data] = this.element[data];
                }

                return result;
            }

            registerCriticalProperty(property) {
                this.criticalDataList.push(property);
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
            const component = new ComponentClass('{$myId}', parentElement, '{$this->selector}');
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

    public function getSelector(): string
    {
        return $this->selector;
    }

    public function querySelector(string $selector): Element
    {
        foreach ($this->childs as $component) {
            if ($component instanceof Element &&
                $component->getSelector() == $selector
            ) {
                return $component;
            }
        }

        $element = new Element($selector);
        $element->setCrawler($this->crawler->filter($selector));

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
        if (! isset($this->properties[$name])) {
            throw new MissingComponentDataException(<<<JAVASCRIPT
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
        $data = [
            'componentId' => $this->getId(),
            'property' => $name,
            'value' => $value,
        ];

        $this->app->invokeJavaScriptFunction(self::class, 'setProperty', $data);

        $this->properties[$name] = $value;
    }

    public function css(string $property, string $value): void
    {
        $data = [
            'componentId' => $this->getId(),
            'property' => $property,
            'value' => $value,
        ];

        $this->app->invokeJavaScriptFunction(self::class, 'setStyle', $data);
    }

    public function updateData(string $key, $value)
    {
        $this->{$key} = $value;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }
}
