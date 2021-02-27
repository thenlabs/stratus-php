<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Tests;

use ThenLabs\PyramidalTests\Utils\StaticVarsInjectionTrait;
use ThenLabs\StratusPHP\AbstractPage;
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
    use StaticVarsInjectionTrait;

    private static $driver;

    public function setUp()
    {
        $this->injectVars();
    }

    public static function getDriver(): RemoteWebDriver
    {
        $capabilities = DesiredCapabilities::chrome();

        if ($_ENV['SELENIUM_BROWSER'] == 'firefox') {
            $capabilities = DesiredCapabilities::firefox();
        }

        if (! self::$driver instanceof RemoteWebDriver) {
            self::$driver = RemoteWebDriver::create($_ENV['SELENIUM_SERVER'], $capabilities);
        }

        return self::$driver;
    }

    public static function dumpApp(AbstractPage $page, bool $runPlugins = true): void
    {
        $class = new ReflectionClass($page);

        if (! $class->isAnonymous()) {
            throw new Exception('The class of the instance is not anonymous.');
        }

        $fileName = $class->getFileName();
        $startLine = $class->getStartLine();
        $endLine = $class->getEndLine();

        $uses = '';
        $useMatches = [];

        preg_match_all('/use +[\w\s\\\]+;/', file_get_contents($fileName), $useMatches);

        foreach ($useMatches[0] as $match) {
            $useSentence = $match . PHP_EOL;

            if (false === strpos($uses, $useSentence)) {
                $uses .= $useSentence;
            }
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
            if (false === strpos($line, 'static::dumpApp($page);') &&
                false === strpos($line, 'static::dumpApp($page, true);') &&
                false === strpos($line, 'static::dumpApp($page, false);')
            ) {
                $rest .= $line;
            } else {
                break;
            }
        }

        (function () {
            $this->updateJavaScriptClasses();
        })->call($page);

        $javaScriptClassesDef = var_export($page->getJavaScriptClasses(), true);

        $classSource = <<<PHP
            <?php

            {$uses}

            class App extends TestApp
            {
            {$members}
            }
        PHP;

        $setDebug = isset($_ENV['DEBUG']) && $_ENV['DEBUG'] == 1 ?
            '$page->setDebug(true);' : ''
        ;

        $runPluginsStr = var_export($runPlugins, true);

        $source = <<<PHP
            <?php

            {$uses}

            require_once 'Page.php';

            \$page = new App('/controller.php', {$runPluginsStr});
            {$setDebug}
            \$page->setJavaScriptClasses({$javaScriptClassesDef});

            {$rest}

            return \$page;
        PHP;

        file_put_contents(__DIR__.'/public/Page.php', $classSource);
        file_put_contents(__DIR__.'/public/page.php', $source);

        fclose($file);
    }

    public static function openApp(): void
    {
        static::getDriver()->get($_ENV['TEST_URL'].'?data='.serialize(static::$vars));
    }

    public static function findElement(string $cssSelector): ?RemoteWebElement
    {
        return static::getDriver()->findElement(WebDriverBy::cssSelector($cssSelector));
    }

    public static function findElements(string $cssSelector): array
    {
        return static::getDriver()->findElements(WebDriverBy::cssSelector($cssSelector));
    }

    public static function executeScript(string $script)
    {
        return static::getDriver()->executeScript($script);
    }

    public static function waitForResponse()
    {
        do {
            $httpRequestsLen = static::executeScript('return stratusAppInstance.httpRequests.length');
        } while ($httpRequestsLen > 0);
    }
}
