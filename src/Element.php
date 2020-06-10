<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP;

use ThenLabs\StratusPHP\Exception\InvokationBeforeBootException;
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

            static removeAttribute(componentId, attribute) {
                app.getComponent(componentId).element.removeAttribute(attribute);
            }

            static addClass(componentId, className) {
                app.getComponent(componentId).element.classList.add(className);
            }

            static removeClass(componentId, className) {
                app.getComponent(componentId).element.classList.remove(className);
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

    private function verifyAppBooted(string $method): void
    {
        if (! $this->app->isBooted()) {
            throw new InvokationBeforeBootException($method);
        }
    }
}
