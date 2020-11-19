<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Plugin\PageDom;

use ThenLabs\StratusPHP\Annotation\OnConstructor;
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
        $this->registerJavaScriptClass(Element::class);
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
            JAVASCRIPT);

            $componentId = $this->executeFrontCall($frontCall, false);

            if ($componentId) {
                $element->setId($componentId);
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

}
