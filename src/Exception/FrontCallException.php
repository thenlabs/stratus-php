<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Exception;

use ThenLabs\StratusPHP\FrontCall;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class FrontCallException extends StratusException
{
    /**
     * @var FrontCall
     */
    protected $frontCall;

    /**
     * @param FrontCall $frontCall
     */
    public function __construct(FrontCall $frontCall)
    {
        $this->frontCall = $frontCall;
    }

    /**
     * @return FrontCall
     */
    public function getFrontCall(): FrontCall
    {
        return $this->frontCall;
    }
}
