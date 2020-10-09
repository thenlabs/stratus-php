<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP;

use ThenLabs\StratusPHP\Event\StratusEvent;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class StratusEventListener
{
    protected $fetchData = [];
    protected $backListener;

    public function setFetchData(array $fetchData): void
    {
        $this->fetchData = $fetchData;
    }

    public function getFetchData(): array
    {
        return $this->fetchData;
    }

    public function setBackListener(callable $backListener): void
    {
        $this->backListener = $backListener;
    }

    public function __invoke(...$args)
    {
        call_user_func_array($this->backListener, $args);
    }
}
