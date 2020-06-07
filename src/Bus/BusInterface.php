<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Bus;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
interface BusInterface
{
    public function open();

    public function write(array $data);

    public function close();
}
