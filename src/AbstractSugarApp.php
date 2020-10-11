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
    public function __construct(string $controllerUri)
    {
        parent::__construct($controllerUri);

        $crawler = new HtmlPageCrawler($this->getView());

        foreach ($crawler->filter('*') as $item) {
            foreach ($item->attributes as $attribute) {
                $matches = [];
                preg_match('/^e-([a-zA-Z][a-zA-Z0-9]+)$/', $attribute->name, $matches);

                if (! empty($matches)) {
                    $componentName = $matches[1];

                    $element = $this->querySelector("[{$matches[0]}]");

                    if ($element) {
                        $element->setName($componentName);

                        $this->{$componentName} = $element;
                    }
                }
            }
        }
    }
}
