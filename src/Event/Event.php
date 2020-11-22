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
    /**
     * @var AbstractApp
     */
    protected $app;

    /**
     * @var array
     */
    protected $eventData = [];

    /**
     * @return AbstractApp
     */
    public function getApp(): AbstractApp
    {
        return $this->app;
    }

    /**
     * @param AbstractApp $app
     */
    public function setApp(AbstractApp $app): void
    {
        $this->app = $app;
    }

    /**
     * @return array
     */
    public function getEventData(): array
    {
        return $this->eventData;
    }

    /**
     * @param array $eventData
     */
    public function setEventData(array $eventData): void
    {
        $this->eventData = $eventData;
    }
}
