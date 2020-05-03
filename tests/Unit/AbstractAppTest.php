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
                    return <<<HTML
                        <!DOCTYPE html>
                        <html lang="en">
                        <head>
                            <meta charset="UTF-8">
                            <title></title>
                        </head>
                        <body>
                            <button class="btn-class-1 btn-class-2"></button>
                        </body>
                        </html>
                    HTML;
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

        testCase(function () {
            setUp(function () {
                $this->buttonElement = $this->app->querySelector('button');
            });

            test(function () {
                $this->assertEquals(
                    'btn-class-1 btn-class-2',
                    $this->buttonElement->getAttribute('class')
                );
            });

            test(function () {
                $this->assertTrue($this->buttonElement->hasClass('btn-class-1'));
                $this->assertTrue($this->buttonElement->hasClass('btn-class-2'));
            });

            test(function () {
                $this->assertFalse($this->buttonElement->hasClass(uniqid()));
            });

            testCase(function () {
                setUp(function () {
                    $this->newClass = uniqid('class-');
                    $this->buttonElement->addClass($this->newClass);
                });

                test(function () {
                    $this->assertEquals(
                        "btn-class-1 btn-class-2 {$this->newClass}",
                        $this->buttonElement->getAttribute('class')
                    );
                });

                test(function () {
                    $this->assertTrue($this->buttonElement->hasClass($this->newClass));
                });
            });
        });
    });
});
