<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Event;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class EventListener
{
    /**
     * The data of the JavaScript event object that should be fetch to the backend.
     *
     * @var array
     */
    protected $fetchData = [];

    /**
     * JavaScript source code used in the browser for handle the event.
     *
     * The front listener is executed before a request will be sent to the server.
     *
     * @var string
     */
    protected $frontListener;

    /**
     * Callable used for handle the event in the server.
     *
     * @var callable
     */
    protected $backListener;

    public function __construct(array $properties = [])
    {
        foreach ($properties as $property => $value) {
            $this->{$property} = $value;
        }
    }

    /**
     * @param array $fetchData
     */
    public function setFetchData(array $fetchData): void
    {
        $this->fetchData = $fetchData;
    }

    /**
     * @return array
     */
    public function getFetchData(): array
    {
        return $this->fetchData;
    }

    /**
     * @param callable $backListener
     */
    public function setBackListener(callable $backListener): void
    {
        $this->backListener = $backListener;
    }

    /**
     * @param string $frontListener
     */
    public function setFrontListener(string $frontListener): void
    {
        $this->frontListener = $frontListener;
    }

    /**
     * @return string|null
     */
    public function getFrontListener(): ?string
    {
        return $this->frontListener;
    }

    /**
     * At invoke this class should be invoked the back listener.
     */
    public function __invoke(...$args)
    {
        call_user_func_array($this->backListener, $args);
    }
}
