<?php
declare(strict_types=1);

namespace ThenLabs\Stratus\Tests;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use ThenLabs\PyramidalTests\Utils\StaticVarsInjectionTrait;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class TestCase extends PHPUnitTestCase
{
    use StaticVarsInjectionTrait;
}