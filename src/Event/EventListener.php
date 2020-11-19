<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Event;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class EventListener
{
    protected $fetchData = [];
    protected $frontListener;
    protected $backListener;

    public function __construct(array $properties = [])
    {
        foreach ($properties as $property => $value) {
            $this->{$property} = $value;
        }
    }

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

    public function setFrontListener(string $frontListener): void
    {
        $this->frontListener = $frontListener;
    }

    public function getFrontListener(): ?string
    {
        return $this->frontListener;
    }

    public function __invoke(...$args)
    {
        call_user_func_array($this->backListener, $args);
    }
}
