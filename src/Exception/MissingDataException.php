<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Exception;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class MissingDataException extends StratusException
{
    protected $script;

    public function __construct(string $script)
    {
        $this->script = $script;
    }

    public function getCollectDataScript(): string
    {
        return $this->script;
    }
}
