<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Event;

use ThenLabs\Components\Event\Event as ComponentsEvent;
use ThenLabs\StratusPHP\AbstractApp;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class Event extends ComponentsEvent
{
    protected $app;
    protected $eventData = [];

    public function getApp(): AbstractApp
    {
        return $this->app;
    }

    public function setApp(AbstractApp $app): void
    {
        $this->app = $app;
    }

    public function getEventData(): array
    {
        return $this->eventData;
    }

    public function setEventData(array $eventData): void
    {
        $this->eventData = $eventData;
    }
}
