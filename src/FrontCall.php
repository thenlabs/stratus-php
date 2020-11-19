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
    protected $queryMode;

    public function __construct(string $script, bool $queryMode)
    {
        $this->script = $script;
        $this->queryMode = $queryMode;

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

    public function getQueryMode(): bool
    {
        return $this->queryMode;
    }
}
