<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Plugin\VueJs;

use ThenLabs\ComposedViews\AbstractCompositeView;
use ThenLabs\ComposedViews\Event\RenderEvent;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use ThenLabs\StratusPHP\AbstractPage;
use ThenLabs\StratusPHP\Component\ComponentInterface;
use ThenLabs\StratusPHP\JavaScript\Utils;
use ReflectionClass;
use ReflectionProperty;
use Exception;

AnnotationRegistry::registerFile(__DIR__.'/Annotation/Data.php');

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 * @abstract
 */
abstract class AbstractComponent extends AbstractCompositeView implements ComponentInterface
{
    /**
     * @var AbstractPage
     */
    protected $page;

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
        return <<<JAVASCRIPT
            constructor(id, vueInstance) {
                this.id = id;
                this.vueInstance = vueInstance;
            }

            getCriticalData() {
                let result = {};

                return result;
            }
        JAVASCRIPT;
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

        $myId = $this->getId();
        $jsClassId = $this->page->getJavaScriptClassId(self::class);

        $options = [
            'el' => ".stratus-vue-container-{$myId}",
            'data' => $data,
        ];

        $jsOptions = Utils::getJavaScriptValue($options);

        return <<<JAVASCRIPT
            {
                const vueInstanceOptions = {$jsOptions};
                const vueInstance = new Vue(vueInstanceOptions);
                const ComponentClass = app.getClass('{$jsClassId}');
                const component = new ComponentClass('{$myId}', vueInstance);

                app.addComponent(component);
            }
        JAVASCRIPT;
    }

    public function __set($name, $value)
    {
        $class = new ReflectionClass($this);
        $property = $class->getProperty($name);

        if (! $property instanceof ReflectionProperty) {
            throw new Exception("Unexistent property with name '{$name} of a vue component.'");
        }

        $reader = new AnnotationReader;
        $dataAnnotation = $reader->getPropertyAnnotation($property, Annotation\Data::class);

        if (! $dataAnnotation instanceof Annotation\Data) {
            throw new Exception("Unexistent property with name '{$name} of a vue component.'");
        }

        $jsValue = Utils::getJavaScriptValue($value);

        $this->page->executeScript(<<<JAVASCRIPT
            const component = stratusAppInstance.getComponent('{$this->getId()}');
            component.vueInstance['{$name}'] = {$jsValue};
        JAVASCRIPT, false);

        $this->{$name} = $value;
    }

    /**
     * @param AbstractPage|null $page
     */
    public function setPage(?AbstractPage $page): void
    {
        $this->page = $page;
    }

    /**
     * @return AbstractPage|null
     */
    public function getPage(): ?AbstractPage
    {
        return $this->page;
    }

    public function updateData(string $key, $value): void
    {
    }

    public function registerCriticalData(string $dataName): void
    {
    }
}
