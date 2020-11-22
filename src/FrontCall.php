<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class FrontCall
{
    /**
     * @var string
     */
    protected $script;

    /**
     * @var string
     */
    protected $hash;

    /**
     * @var boolean
     */
    protected $queryMode;

    /**
     * @param string  $script
     * @param boolean $queryMode
     */
    public function __construct(string $script, bool $queryMode)
    {
        $this->script = $script;
        $this->queryMode = $queryMode;

        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        $this->hash = md5(serialize(compact('backtrace')));
    }

    /**
     * @return string
     */
    public function getScript(): string
    {
        return $this->script;
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * @return boolean
     */
    public function getQueryMode(): bool
    {
        return $this->queryMode;
    }
}
