<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Component;

use ThenLabs\StratusPHP\AbstractApp;
use ThenLabs\StratusPHP\JavaScript\JavaScriptInstanceInterface;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
interface ComponentInterface extends JavaScriptInstanceInterface
{
    /**
     * @param AbstractApp|null $app
     */
    public function setApp(?AbstractApp $app): void;

    /**
     * @return AbstractApp|null
     */
    public function getApp(): ?AbstractApp;

    /**
     * Update a data of the component.
     *
     * @param  string $key   data name.
     * @param  mixed  $value data value.
     */
    public function updateData(string $key, $value): void;

    /**
     * Register a critical data of the component.
     *
     * The critical data of a component are sent in every request.
     *
     * @param  string $dataName
     */
    public function registerCriticalData(string $dataName): void;
}
