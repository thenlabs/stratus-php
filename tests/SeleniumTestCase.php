<?php
declare(strict_types=1);

namespace ThenLabs\Stratus\Tests;

use ThenLabs\Stratus\AbstractApp;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebElement;
use ReflectionClass;
use Exception;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class SeleniumTestCase extends TestCase
{
    private static $driver;

    public static function getDriver(): RemoteWebDriver
    {
        if (! self::$driver instanceof RemoteWebDriver) {
            self::$driver = RemoteWebDriver::create(
                $_ENV['SELENIUM_SERVER'],
                DesiredCapabilities::chrome()
            );
        }

        return self::$driver;
    }

    public static function dumpApp(AbstractApp $app): void
    {
        $class = new ReflectionClass($app);

        if (! $class->isAnonymous()) {
            throw new Exception('The class of the instance is not anonymous.');
        }

        $fileName = $class->getFileName();
        $startLine = $class->getStartLine();
        $endLine = $class->getEndLine();

        $uses = '';
        $useMatches = [];

        preg_match_all('/use +[\w\\\]+;/', file_get_contents($fileName), $useMatches);
        foreach ($useMatches[0] as $match) {
            $uses .= $match . PHP_EOL;
        }

        $file = fopen($fileName, 'r');
        $members = '';

        for ($currentLine = 0; $currentLine < $endLine - 1; $currentLine++) {
            $line = fgets($file);

            if ($currentLine >= $startLine) {
                $members .= $line;
            }
        }

        $closingBracket = fgets($file);
        $currentLine++;

        if (trim($closingBracket) != '};') {
            throw new Exception("Expecting '};' in line {$currentLine}.");
        }

        $rest = '';
        while ($line = fgets($file)) {
            if (false === strpos($line, 'static::dumpApp($app);')) {
                $rest .= $line;
            } else {
                break;
            }
        }

        $source = <<<PHP
        <?php

        {$uses}

        class App extends AbstractApp
        {
        {$members}
        }

        {$rest}
        PHP;

        file_put_contents(__DIR__.'/public/app.php', $source);

        fclose($file);
    }

    public static function openApp(): void
    {
    }

    public static function findElement(string $cssSelector): ?RemoteWebElement
    {
        return static::getDriver()->findElement(WebDriverBy::cssSelector($cssSelector));
    }

    public static function findElements(string $cssSelector): array
    {
        return static::getDriver()->findElements(WebDriverBy::cssSelector($cssSelector));
    }
}
