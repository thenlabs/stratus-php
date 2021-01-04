<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Component;

use ThenLabs\StratusPHP\AbstractPage;
use ThenLabs\StratusPHP\JavaScript\JavaScriptInstanceInterface;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
interface ComponentInterface extends JavaScriptInstanceInterface
{
    /**
     * @param AbstractPage|null $page
     */
    public function setPage(?AbstractPage $page): void;

    /**
     * @return AbstractPage|null
     */
    public function getPage(): ?AbstractPage;

    /**
     * Update a data of the component.
     *
     * @param string $key   data name.
     * @param mixed  $value data value.
     */
    public function updateData(string $key, $value): void;

    /**
     * Register a critical data of the component.
     *
     * The critical data of a component are sent in every request.
     *
     * @param string $dataName
     */
    public function registerCriticalData(string $dataName): void;
}
