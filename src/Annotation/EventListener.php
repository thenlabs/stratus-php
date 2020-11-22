<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Annotation;

/**
 * @Annotation
 *
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class EventListener
{
    /**
     * @var array
     */
    public $fetchData = [];

    /**
     * @var string
     */
    public $frontListener;
}
