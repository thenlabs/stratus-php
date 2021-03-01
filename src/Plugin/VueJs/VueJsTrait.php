<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Plugin\VueJs;

use ThenLabs\StratusPHP\Annotation\OnConstructor;
use Doctrine\Common\Annotations\AnnotationRegistry;

AnnotationRegistry::registerFile(__DIR__.'/Annotation/Data.php');

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
trait VueJsTrait
{
    /**
     * @OnConstructor
     */
    public function runPluginVueJs(): void
    {
    }
}
