<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Messaging\Bus;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
interface BusInterface
{
    public function write(array $data);
}
