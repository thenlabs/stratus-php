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

    protected $cssSelector;
    protected $attributes = [];
    protected $crawler;

    public function __construct(string $cssSelector)
    {
        $this->cssSelector = $cssSelector;
    }

    public static function getJavaScriptClassMembers(): string
    {
        return <<<JAVASCRIPT
        JAVASCRIPT;
    }

    public function getJavaScriptCreateInstanceScript(): string
    {
        $jsAttributes = '';

        foreach ($this->attributes as $attribute => $value) {
            if ($value === null) {
                continue;
            }

            $jsAttribute = var_export($attribute, true);
            $jsValue = var_export($value, true);

            $jsAttributes .= <<<JAVASCRIPT
                element.setAttribute({$jsAttribute}, {$jsValue});\n
            JAVASCRIPT;
        }

        return <<<JAVASCRIPT
            var element = document.querySelector('{$this->cssSelector}');
            {$jsAttributes}
        JAVASCRIPT;
    }

    public function click(): void
    {
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
        // foreach ($this->childs as $component) {
        //     if ($component instanceof Element &&
        //         $component->getCssSelector() == $cssSelector
        //     ) {
        //         return $component;
        //     }
        // }

        $element = new Element($cssSelector);
        $element->setCrawler($this->crawler->filter($cssSelector));

        $this->addChild($element);

        return $element;
    }
}
