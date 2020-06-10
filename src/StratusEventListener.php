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

    public function __construct(array $requiredEventData = [])
    {
        $this->requiredEventData = $requiredEventData;
    }

    public function getRequiredEventData(): array
    {
        return $this->requiredEventData;
    }

    public function onBack(StratusEvent $event): void
    {
    }

    public function __invoke()
    {
        call_user_func([$this, 'onBack']);
    }
}
