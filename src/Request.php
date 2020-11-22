<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class Request
{
    /**
     * @var string
     */
    protected $token;

    /**
     * @var array
     */
    protected $componentData;

    /**
     * @var string
     */
    protected $eventName;

    /**
     * @var array
     */
    protected $eventData = [];

    /**
     * @var array
     */
    protected $executedFrontCalls = [];

    /**
     * @var boolean
     */
    protected $capture = false;

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @param string $token
     */
    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    /**
     * @return array
     */
    public function getComponentData(): array
    {
        return $this->componentData;
    }

    /**
     * @param array $componentData
     */
    public function setComponentData(array $componentData): void
    {
        $this->componentData = $componentData;
    }

    /**
     * @return array|null
     */
    public function getExecutedFrontCalls(): ?array
    {
        return $this->executedFrontCalls;
    }

    /**
     * @param array|null $executedFrontCalls
     */
    public function setExecutedFrontCalls(?array $executedFrontCalls): void
    {
        $this->executedFrontCalls = $executedFrontCalls;
    }

    /**
     * @param  string $hash
     * @param  mixed  $value
     */
    public function registerFrontCallResult(string $hash, $value): void
    {
        $this->executedFrontCalls[$hash] = $value;
    }

    /**
     * @return string
     */
    public function getEventName(): string
    {
        return $this->eventName;
    }

    /**
     * @param string $eventName
     */
    public function setEventName(string $eventName): void
    {
        $this->eventName = $eventName;
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

    /**
     * @param boolean $capture
     */
    public function setCapture(bool $capture): void
    {
        $this->capture = $capture;
    }

    /**
     * @return boolean
     */
    public function isCapture(): bool
    {
        return $this->capture;
    }

    /**
     * @param  string $json
     * @return Request
     */
    public static function createFromJson(string $json): self
    {
        $data = json_decode($json, true);

        $request = new self;
        $request->setToken($data['token']);
        $request->setComponentData($data['componentData']);
        $request->setEventName($data['eventName']);
        $request->setEventData($data['eventData']);
        $request->setCapture($data['capture']);

        if (isset($data['executedFrontCalls'])) {
            $request->setExecutedFrontCalls($data['executedFrontCalls']);
        }

        return $request;
    }
}
