<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Bus;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
interface BusInterface
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
