<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Plugin\VueJs;

use ThenLabs\ComposedViews\AbstractCompositeView;
use ThenLabs\ComposedViews\Event\RenderEvent;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use ThenLabs\StratusPHP\JavaScript\JavaScriptInstanceInterface;
use ThenLabs\StratusPHP\JavaScript\Utils;

AnnotationRegistry::registerFile(__DIR__.'/Annotation/Data.php');

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 * @abstract
 */
abstract class AbstractComponent extends AbstractCompositeView implements JavaScriptInstanceInterface
{
    public function __construct()
    {
        parent::__construct();

        $this->addFilter([$this, '_addContainerElement']);
    }

    public function _addContainerElement(RenderEvent $event): void
    {
        $event->setView(<<<HTML
            <div class="stratus-vue-container stratus-vue-container-{$this->getId()}">
                {$event->getView()}
            </div>
        HTML);
    }

    /**
     * @return array
     */
    public function getOwnDependencies(): array
    {
        return [
            'vuejs' => Asset\VueJsScript::getInstance(),
        ];
    }

    /**
     * @return string
     */
    public static function getJavaScriptClassMembers(): string
    {
        return '';
    }

    /**
     * @return string
     */
    public function getJavaScriptCreateInstanceScript(): string
    {
        $class = new \ReflectionClass($this);
        $reader = new AnnotationReader;

        $data = [];

        foreach ($class->getProperties() as $property) {
            foreach ($reader->getPropertyAnnotations($property) as $annotation) {
                if ($annotation instanceof Annotation\Data) {
                    $property->setAccessible(true);
                    $data[$property->getName()] = $property->getValue($this);
                }
            }
        }

        $options = [
            'el' => ".stratus-vue-container-{$this->getId()}",
            'data' => $data,
        ];

        $jsOptions = Utils::getJavaScriptValue($options);

        return <<<JAVASCRIPT
            const options = {$jsOptions};
            new Vue(options);
        JAVASCRIPT;
    }
}
