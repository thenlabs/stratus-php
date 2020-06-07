<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class StratusRequest
{
    protected $token;
    protected $componentData;
    protected $eventName;
    protected $operations = [];

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

    public function getOperations(): ?array
    {
        return $this->operations;
    }

    public function setOperations(?array $operations): void
    {
        $this->operations = $operations;
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
        $data = json_decode($json, true);

        $request = new self;
        $request->setToken($data['token']);
        $request->setComponentData($data['componentData']);
        $request->setEventName($data['eventName']);

        if (isset($data['operations'])) {
            $request->setOperations($data['operations']);
        }

        return $request;
    }
}
