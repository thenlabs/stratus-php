<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Exception;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class MissingComponentDataException extends StratusException
{
    protected $javaScript;

    public function __construct(string $javaScript)
    {
        $this->javaScript = $javaScript;
    }

    public function getJavaScript(): string
    {
        return $this->javaScript;
    }
}
