<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP;

use ThenLabs\ComposedViews\Event\RenderEvent;
use ThenLabs\StratusPHP\Annotation\StratusEventListener as StratusEventListenerAnnotation;
use Wa72\HtmlPageDom\HtmlPageCrawler;
use Doctrine\Common\Annotations\AnnotationReader;
use ReflectionClass;

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
        $builtElements = [];

        foreach ($crawler->filter("[{$this->attributeForElements}]") as $item) {
            $componentName = $item->getAttribute($this->attributeForElements);

            $element = $this->querySelector("[{$this->attributeForElements}=\"{$componentName}\"]");

            if ($element) {
                $element->setName($componentName);
                $element->setJsVarName($componentName);

                $this->{$componentName} = $element;
                $builtElements[$componentName] = $element;
            }
        }

        if (! empty($builtElements)) {
            $class = new ReflectionClass($this);
            $namesOfBuiltElements = array_keys($builtElements);

            foreach ($class->getMethods() as $method) {
                $methodName = $method->getName();
                $pattern = '/^on([a-zA-Z0-9_]+)('.implode('|', $namesOfBuiltElements).')$/i';
                $matches = [];

                if (preg_match($pattern, $methodName, $matches)) {
                    $eventName = strtolower($matches[1]);
                    $componentName = $matches[2];

                    $element = null;
                    foreach ($builtElements as $key => $value) {
                        if (0 === strcasecmp($key, $componentName)) {
                            $element = $builtElements[$key];
                            break;
                        }
                    }

                    $eventListener = new StratusEventListener;
                    $eventListener->setBackListener([$this, $methodName]);

                    $annotationReader = new AnnotationReader;
                    if ($annotation = $annotationReader->getMethodAnnotation($method, StratusEventListenerAnnotation::class)) {
                        if ($frontListener = $annotation->frontListener) {
                            $lines = explode(PHP_EOL, $frontListener);
                            if (count($lines) > 1) {
                                array_walk($lines, function (string &$line) {
                                    $line = ltrim($line, ' *');
                                    $line = trim($line);
                                });

                                $frontListener = implode('', $lines);
                            }

                            $eventListener->setFrontListener($frontListener);
                        }
                    }

                    $element->addEventListener($eventName, $eventListener);
                }
            }
        }
    }
}
