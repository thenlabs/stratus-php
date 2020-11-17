<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Plugin\PageDom;

use ThenLabs\StratusPHP\Annotation\OnConstructor;
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

    public function querySelector(string $selector, bool $registerOperation = true): Element
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

            if ($registerOperation) {
                $this->operations[] = [
                    'type' => 'querySelector',
                    'data' => [
                        'id' => $element->getId(),
                        'parent' => null,
                        'selector' => $selector,
                    ]
                ];

                $this->invokeJavaScriptFunction(Element::class, 'createNew', [
                    'classId' => $this->getJavaScriptClassId(Element::class),
                    'componentId' => $element->getId(),
                    'parent' => null,
                    'selector' => $selector,
                ]);
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
