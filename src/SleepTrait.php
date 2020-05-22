<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP;

use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
trait SleepTrait
{
    public function __sleep()
    {
        $sanatizeDispatcher = function () {
            $this->sorted = [];
            $this->optimized = null;
        };

        if ($this->eventDispatcher instanceof EventDispatcher) {
            $sanatizeDispatcher->call($this->eventDispatcher);
        }

        if ($this->captureEventDispatcher instanceof EventDispatcher) {
            $sanatizeDispatcher->call($this->captureEventDispatcher);
        }

        $vars = get_object_vars($this);
        $result = array_diff(array_keys($vars), $this->nonSerializableProperties);

        return $result;
    }
}
