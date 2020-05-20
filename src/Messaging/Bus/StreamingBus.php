<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Messaging\Bus;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class StreamingBus implements BusInterface
{
    public function open()
    {
    }

    public function write(array $data)
    {
        echo json_encode($data) . '%SSS%';

        ob_flush();
        flush();
    }

    public function close()
    {
        die;
    }
}
