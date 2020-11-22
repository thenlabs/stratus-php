<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Plugin\PageDom;

use ThenLabs\StratusPHP\Annotation\OnConstructor;
use ThenLabs\StratusPHP\Event\SleepChildEvent;
use ThenLabs\StratusPHP\FrontCall;
use Wa72\HtmlPageDom\HtmlPageCrawler;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
trait PageDomTrait
{
    /**
     * @OnConstructor
     */
    public function runPluginPageDom(): void
    {
        $this->classListWithTotalInsertionCapability[] = Element::class;

        $this->registerJavaScriptClass(Element::class);

        $this->eventDispatcher->addListener(SleepChildEvent::class, [$this, '_sleepElements']);
    }

    /**
     * @param  string $selector css selector.
     * @return Element
     */
    public function querySelector(string $selector): Element
    {
        foreach ($this->childs as $component) {
            if ($component instanceof Element &&
                $component->getSelector() == $selector
            ) {
                return $component;
            }
        }

        if ($this->booted) {
            $element = new Element($selector);
            $element->setApp($this);

            $this->addChild($element);

            $elementJavaScriptClassId = $this->getJavaScriptClassId(Element::class);

            $frontCall = new FrontCall(<<<JAVASCRIPT
                const StratusElement = stratusAppInstance.classes['{$elementJavaScriptClassId}'];

                const component = new StratusElement(
                    '{$element->getId()}',
                    stratusAppInstance.rootElement,
                    '{$selector}'
                );

                stratusAppInstance.addComponent(component);

                return '{$element->getId()}';
            JAVASCRIPT, true);

            $componentId = $this->executeFrontCall($frontCall, false);

            if ($componentId) {
                $element->setId($componentId);

                $componentData = $this->currentRequest->getComponentData()[$componentId] ?? null;
                if (is_array($componentData)) {
                    foreach ($componentData as $key => $value) {
                        $element->updateData($key, $value);
                    }
                }
            }

            return $element;
        } else {
            $hasInmutableView = $this->hasInmutableView();

            $view = $hasInmutableView ? $this->inmutableView : $this->render();
            $crawler = new HtmlPageCrawler($view);
            $elementCrawler = $crawler->filter($selector);

            $element = new Element($selector);
            $element->setCrawler($elementCrawler);
            $element->setApp($this);

            $this->addChild($element);

            if (! $hasInmutableView) {
                $this->inmutableView = $view;
            }

            return $element;
        }
    }

    public function _sleepElements(SleepChildEvent $event): void
    {
        $child = $event->getChild();

        if ($child instanceof Element) {
            $child->setCrawler(null);

            (function () {
                $this->criticalProperties = [];
            })->call($child);
        }
    }
}
