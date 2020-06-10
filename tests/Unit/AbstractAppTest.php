<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Tests\Unit;

use ThenLabs\StratusPHP\AbstractApp;
use ThenLabs\StratusPHP\StratusRequest;
use ThenLabs\StratusPHP\Exception\InmutableViewException;
use ThenLabs\StratusPHP\Exception\InvalidTokenException;
use ThenLabs\StratusPHP\Tests\TestCase;
use ThenLabs\Components\ComponentInterface;
use ThenLabs\Components\ComponentTrait;

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
            $this->assertFalse($this->app->hasInmutableView());
        });

        test(function () {
            $this->assertNull($this->app->getJavaScriptClassId(uniqid('Class')));
        });

        test(function () {
            $this->expectException(InvalidTokenException::class);

            $request = new StratusRequest;
            $request->setToken(uniqid());

            $this->app->run($request);
        });

        test(function () {
            $this->expectException(InvalidTokenException::class);

            $request = new StratusRequest;
            $request->setToken(uniqid());

            $this->app->run($request);
        });

        test(function () {
            $this->body = $this->app->querySelector('body');
            $this->button = $this->body->querySelector('button');

            $this->assertSame($this->app, $this->body->getApp());
            $this->assertSame($this->app, $this->button->getApp());
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
                $this->assertTrue($this->buttonElement->hasClass('btn-class-1'));
                $this->assertTrue($this->buttonElement->hasClass('btn-class-2'));
            });

            test(function () {
                $this->assertFalse($this->buttonElement->hasClass(uniqid()));
            });

            test(function () {
                $this->assertSame($this->buttonElement, $this->app->querySelector('button'));
            });

            test(function () {
                $this->assertTrue($this->app->hasInmutableView());
            });

            test(function () {
                $this->assertSame($this->app, $this->buttonElement->getApp());
            });

            testCase(function () {
                setUp(function () {
                    $this->newClass = uniqid('class-');
                    $this->buttonElement->addClass($this->newClass);
                });

                test(function () {
                    $this->assertTrue($this->buttonElement->hasClass($this->newClass));
                });
            });

            testCase(function () {
                setUp(function () {
                    $this->expectException(InmutableViewException::class);
                });

                test(function () {
                    $this->app->addFilter(function () {
                    });
                });

                test(function () {
                    $child = new class implements ComponentInterface {
                        use ComponentTrait;
                    };

                    $this->app->addChild($child);
                });
            });
        });
    });
});
