<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Tests;

use ThenLabs\StratusPHP\AbstractApp;

setTestCaseNamespace(__NAMESPACE__);
setTestCaseClass(SeleniumTestCase::class);

testCase('AbstractAppTest.php', function () {
    testCase('create a new app instance', function () {
        setUp(function () {
            $this->controllerUri = uniqid('controllerUri');

            $this->app = new class($this->controllerUri) extends AbstractApp {
                public function getView(): string
                {
                    return '';
                }
            };
        });

        test(function () {
            $this->assertEquals($this->controllerUri, $this->app->getControllerUri());
        });

        test(function () {
            $token = $this->app->getToken();

            $this->assertStringStartsWith('token', $token);
            $this->assertGreaterThan(23, strlen($token));
        });

        test(function () {
            $this->assertFalse($this->app->isDebug());
        });

        test(function () {
            $this->app->setDebug(true);

            $this->assertTrue($this->app->isDebug());
        });
    });
});
