<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Plugin\VueJs;

use ThenLabs\ComposedViews\AbstractCompositeView;
use Doctrine\Common\Annotations\AnnotationRegistry;
use ThenLabs\StratusPHP\JavaScript\JavaScriptInstanceInterface;

AnnotationRegistry::registerFile(__DIR__.'/Annotation/Data.php');

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 * @abstract
 */
abstract class AbstractComponent extends AbstractCompositeView implements JavaScriptInstanceInterface
{
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
        return <<<JAVASCRIPT
            new Vue({
                el: '#{$this->getId()}',
                data: {
                }
            });
        JAVASCRIPT;
    }
}
