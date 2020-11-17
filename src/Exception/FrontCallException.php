<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Exception;

use ThenLabs\StratusPHP\FrontCall;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class FrontCallException extends StratusException
{
    protected $frontCall;

    public function __construct(FrontCall $frontCall)
    {
        $this->frontCall = $frontCall;
    }

    public function getFrontCall(): FrontCall
    {
        return $this->frontCall;
    }
}
