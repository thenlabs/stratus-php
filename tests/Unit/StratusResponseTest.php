<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Tests\Unit;

use ThenLabs\StratusPHP\StratusResponse;
use ThenLabs\StratusPHP\Tests\TestCase;

setTestCaseNamespace(__NAMESPACE__);
setTestCaseClass(TestCase::class);

testCase('StratusResponseTest.php', function () {
    test(function () {
        $result = new StratusResponse;

        $this->assertTrue($result->isSuccessful());
    });

    test(function () {
        $result = new StratusResponse;

        $result->setSuccessful(false);

        $this->assertFalse($result->isSuccessful());
    });
});
