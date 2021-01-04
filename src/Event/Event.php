<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Event;

use ThenLabs\Components\Event\Event as ComponentsEvent;
use ThenLabs\StratusPHP\AbstractPage;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class Event extends ComponentsEvent
{
    /**
     * @var AbstractPage
     */
    protected $page;

    /**
     * @var array
     */
    protected $eventData = [];

    /**
     * @return AbstractPage
     */
    public function getPage(): AbstractPage
    {
        return $this->page;
    }

    /**
     * @param AbstractPage $page
     */
    public function setPage(AbstractPage $page): void
    {
        $this->page = $page;
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
