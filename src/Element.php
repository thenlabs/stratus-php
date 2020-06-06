<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP;

use ThenLabs\StratusPHP\Exception\MissingDataException;
use ThenLabs\Components\CompositeComponentInterface;
use ThenLabs\Components\CompositeComponentTrait;
use Wa72\HtmlPageDom\HtmlPageCrawler;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class Element implements CompositeComponentInterface, StratusComponentInterface, QuerySelectorInterface
{
    use CompositeComponentTrait;
    use QuerySelectorAllImplementationPendingTrait;

    protected $selector;
    protected $properties = [];
    protected $crawler;
    protected $app;

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
                this.criticalDataList = [];
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
                eval(`element.\${property} = \${value}`);
            }

            static setAttribute(componentId, attribute, value) {
                app.getComponent(componentId).element.setAttribute(attribute, value);
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

        $node = $this->crawler->getNode(0);
        if ($node->hasAttributes()) {
            foreach ($node->attributes as $attr) {
                $attribute = $attr->nodeName;
                $value = $attr->nodeValue;

                $jsAttribute = var_export($attribute, true);
                $jsValue = var_export($value, true);

                $jsAttributes .= <<<JAVASCRIPT
                    component.element.setAttribute({$jsAttribute}, {$jsValue});\n
                JAVASCRIPT;
            }
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
        if ($this->app->isBooted()) {
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
        } else {
            $this->crawler->setAttribute($attribute, $value);
        }
    }

    public function getAttribute(string $attribute)
    {
        return $this->app->isBooted() ?
            $this->attributes[$attribute] :
            $this->crawler->getAttribute($attribute)
        ;
    }

    public function hasClass(string $cssClass): bool
    {
        return $this->crawler->hasClass($cssClass);
    }

    public function addClass(string $cssClass): void
    {
        $this->crawler->addClass($cssClass);
    }

    public function setStyle(string $property, string $value): void
    {
        $this->setPropertyOnFront("style.{$property}", $value);
    }

    public function getStyle(string $property): string
    {
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

    public function updateData(string $key, $value): void
    {
        $this->properties[$key] = $value;
    }

    private function setPropertyOnFront(string $property, $value): void
    {
        $data = [
            'componentId' => $this->getId(),
            'property' => $property,
            'value' => var_export($value, true),
        ];

        $this->app->invokeJavaScriptFunction(self::class, 'setProperty', $data);
    }
}
