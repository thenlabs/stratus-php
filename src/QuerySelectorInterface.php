<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
interface QuerySelectorInterface
{
    public function querySelector(string $selector): Element;

    public function querySelectorAll(string $selector): array;
}
