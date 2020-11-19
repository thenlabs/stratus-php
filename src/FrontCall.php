<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class FrontCall
{
    protected $script;
    protected $hash;

    public function __construct(string $script)
    {
        $this->script = $script;

        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        $this->hash = md5(serialize(compact('backtrace')));
    }

    public function getScript(): string
    {
        return $this->script;
    }

    public function getHash(): string
    {
        return $this->hash;
    }
}
