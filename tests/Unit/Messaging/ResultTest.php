<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Tests\Unit\Messaging;

use ThenLabs\StratusPHP\Messaging\Result;
use ThenLabs\StratusPHP\Tests\TestCase;

setTestCaseNamespace(__NAMESPACE__);
setTestCaseClass(TestCase::class);

testCase('ResultTest.php', function () {
    test(function () {
        $result = new Result;

        $this->assertTrue($result->isSuccessful());
    });

    test(function () {
        $result = new Result;

        $result->setSuccessful(false);

        $this->assertFalse($result->isSuccessful());
    });
});
