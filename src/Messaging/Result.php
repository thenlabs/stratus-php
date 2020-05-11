<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Messaging;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class Result
{
    protected $successful = true;

    public function isSuccessful(): bool
    {
        return $this->successful;
    }

    public function setSuccessful(bool $successful): void
    {
        $this->successful = $successful;
    }
}
