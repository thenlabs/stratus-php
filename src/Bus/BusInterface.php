<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Bus;

use ThenLabs\StratusPHP\JavaScript\JavaScriptClassInterface;
use ThenLabs\StratusPHP\JavaScript\JavaScriptInstanceInterface;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
interface BusInterface extends JavaScriptClassInterface, JavaScriptInstanceInterface
{
    /**
     * This method should be called before starts to write in the bus.
     */
    public function open();

    /**
     * Send data across the bus.
     *
     * @param array $data
     */
    public function write(array $data);

    /**
     * Calls this method when the bus will not be used anymore.
     */
    public function close();
}
