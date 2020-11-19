<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Event;

use ThenLabs\Components\ComponentInterface;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class SleepChildEvent extends Event
{
    protected $child;

    public function __construct(ComponentInterface $child)
    {
        $this->child = $child;
    }

    public function getChild(): ComponentInterface
    {
        return $this->child;
    }
}
