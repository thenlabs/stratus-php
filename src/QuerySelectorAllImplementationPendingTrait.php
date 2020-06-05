<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
trait QuerySelectorAllImplementationPendingTrait
{
    public function querySelectorAll(string $selector): array
    {
        throw new Exception\StratusException('The method "querySelectorAll" is not implemented yet.');
    }
}