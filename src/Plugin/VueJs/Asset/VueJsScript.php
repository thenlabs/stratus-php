<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Plugin\VueJs\Asset;

use ThenLabs\ComposedViews\Asset\Script;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class VueJsScript extends Script
{
    /**
     * @var self
     */
    private static $instance;

    /**
     * Singleton
     */
    private function __construct()
    {
        parent::__construct('vuejs', null, '');
    }

    /**
     * @return self
     */
    public static function getInstance(): self
    {
        if (! self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * @param string $uri
     */
    public function setUri(string $uri): void
    {
        $this->uri = $uri;
    }
}
