<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Messaging;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class Request
{
    protected $token;
    protected $componentData;
    protected $eventName;

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    public function getComponentData(): array
    {
        return $this->componentData;
    }

    public function setComponentData(array $componentData): void
    {
        $this->componentData = $componentData;
    }

    public function getEventName(): string
    {
        return $this->eventName;
    }

    public function setEventName(string $eventName): void
    {
        $this->eventName = $eventName;
    }

    public static function createFromJson(string $json): self
    {
        $data = json_decode($json);

        $request = new self;
        $request->setToken($data->token);
        $request->setComponentData($data->componentData);
        $request->setEventName($data->eventName);

        return $request;
    }
}
