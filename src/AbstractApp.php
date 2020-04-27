<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class AbstractApp
{
    public function filter(): Element
    {
        return new Element;
    }
}
