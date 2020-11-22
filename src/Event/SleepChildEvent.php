<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Event;

use ThenLabs\Components\ComponentInterface;

/**
 * An event of this type is triggered before that the instance will be serialized.
 *
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class SleepChildEvent extends Event
{
    /**
     * @var ComponentInterface
     */
    protected $child;

    /**
     * @param ComponentInterface $child
     */
    public function __construct(ComponentInterface $child)
    {
        $this->child = $child;
    }

    /**
     * @return ComponentInterface
     */
    public function getChild(): ComponentInterface
    {
        return $this->child;
    }
}
