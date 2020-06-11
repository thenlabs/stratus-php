<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP;

use ThenLabs\StratusPHP\Event\StratusEvent;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class StratusEventListener
{
    protected $requiredEventData = [];
    protected $backListener;

    public function __construct(array $requiredEventData = [])
    {
        $this->requiredEventData = $requiredEventData;
    }

    public function getRequiredEventData(): array
    {
        return $this->requiredEventData;
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
