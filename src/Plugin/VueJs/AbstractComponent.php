<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Plugin\VueJs;

use ThenLabs\ComposedViews\AbstractCompositeView;
use Doctrine\Common\Annotations\AnnotationRegistry;

AnnotationRegistry::registerFile(__DIR__.'/Annotation/Data.php');

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 * @abstract
 */
abstract class AbstractComponent extends AbstractCompositeView
{
    public function getOwnDependencies(): array
    {
        return [
            'vuejs' => Asset\VueJsScript::getInstance(),
        ];
    }
}
