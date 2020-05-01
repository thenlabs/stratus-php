<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class Element
{
    protected $cssSelector;

    public function __construct(string $cssSelector)
    {
        $this->cssSelector = $cssSelector;
    }

    public function click()
    {
    }
}
