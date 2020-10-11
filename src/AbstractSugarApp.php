<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP;

use ThenLabs\ComposedViews\Event\RenderEvent;
use Wa72\HtmlPageDom\HtmlPageCrawler;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 * @abstract
 */
abstract class AbstractSugarApp extends AbstractApp
{
    protected $attributeForElements = 's-elem';

    public function __construct(string $controllerUri)
    {
        parent::__construct($controllerUri);

        $crawler = new HtmlPageCrawler($this->getView());

        foreach ($crawler->filter("[{$this->attributeForElements}]") as $item) {
            $componentName = $item->getAttribute($this->attributeForElements);

            $element = $this->querySelector("[{$this->attributeForElements}=\"{$componentName}\"]");

            if ($element) {
                $element->setName($componentName);

                $this->{$componentName} = $element;
            }
        }
    }
}
