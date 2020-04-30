<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Tests\Unit;

use ThenLabs\StratusPHP\AbstractApp;
use ThenLabs\StratusPHP\Tests\TestCase;

setTestCaseNamespace(__NAMESPACE__);
setTestCaseClass(TestCase::class);

testCase('AbstractAppTest.php', function () {
    testCase(function () {
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
            $this->assertFalse($this->app->isBooted());
        });

        test(function () {
            $this->assertNull($this->app->getJavaScriptClassId(uniqid('Class')));
        });

        testCase(function () {
            setUp(function () {
                $this->app->setDebug(true);
            });

            test(function () {
                $this->assertTrue($this->app->isDebug());
            });

            testCase(function () {
                setUp(function () {
                    $this->className = uniqid('Class');

                    $this->app->registerJavaScriptClass($this->className);
                });

                test(function () {
                    $this->assertSame(
                        $this->className,
                        $this->app->getJavaScriptClassId($this->className)
                    );
                });
            });
        });

        testCase(function () {
            setUp(function () {
                $this->className = uniqid('Class');

                $this->app->registerJavaScriptClass($this->className);
            });

            test(function () {
                $jsClassId = $this->app->getJavaScriptClassId($this->className);

                $this->assertNotEquals($this->className, $jsClassId);
                $this->assertStringStartsWith('Class', $jsClassId);
                $this->assertEquals(18, strlen($jsClassId));
            });
        });

        testCase(function () {
            setUp(function () {
                $this->app->setBooted(true);
            });

            test(function () {
                $this->assertTrue($this->app->isBooted());
            });
        });
    });
});
