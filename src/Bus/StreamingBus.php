<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Bus;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class StreamingBus implements BusInterface
{
    /**
     * {@inheritdoc}
     */
    public function open()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $data)
    {
        echo json_encode($data).'%SSS%';

        ob_flush();
        flush();
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        die;
    }
}
