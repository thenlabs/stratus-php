<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Tests\Unit;

use ThenLabs\StratusPHP\Response;
use ThenLabs\StratusPHP\Tests\TestCase;

setTestCaseNamespace(__NAMESPACE__);
setTestCaseClass(TestCase::class);

testCase('ResponseTest.php', function () {
    test(function () {
        $result = new Response;

        $this->assertTrue($result->isSuccessful());
    });

    test(function () {
        $result = new Response;

        $result->setSuccessful(false);

        $this->assertFalse($result->isSuccessful());
    });
});
