<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Exception;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class InvokationBeforeBootException extends StratusException
{
    public function __construct(string $method)
    {
        parent::__construct("The '{$method}' method can only be called once the app has been booted.");
    }
}
