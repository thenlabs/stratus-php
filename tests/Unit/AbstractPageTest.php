<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Tests\Unit;

use ThenLabs\StratusPHP\AbstractPage;
use ThenLabs\StratusPHP\Request;
use ThenLabs\StratusPHP\Exception\InmutableViewException;
use ThenLabs\StratusPHP\Exception\InvalidTokenException;
use ThenLabs\StratusPHP\Tests\TestCase;
use ThenLabs\Components\ComponentInterface;
use ThenLabs\Components\ComponentTrait;

setTestCaseNamespace(__NAMESPACE__);
setTestCaseClass(TestCase::class);

testCase('AbstractPageTest.php', function () {
    testCase(function () {
        setUp(function () {
            $this->ajaxControllerUri = uniqid('ajaxControllerUri');

            $this->page = new class($this->ajaxControllerUri) extends AbstractPage {
                use \ThenLabs\StratusPHP\Plugin\PageDom\PageDomTrait;

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
            $this->assertEquals($this->ajaxControllerUri, $this->page->getAjaxControllerUri());
        });

        test(function () {
            $token = $this->page->getToken();

            $this->assertStringStartsWith('token', $token);
            $this->assertGreaterThan(23, strlen($token));
        });

        test(function () {
            $this->assertFalse($this->page->isDebug());
        });

        test(function () {
            $this->assertFalse($this->page->isBooted());
        });

        test(function () {
            $this->assertFalse($this->page->hasInmutableView());
        });

        test(function () {
            $this->assertNull($this->page->getJavaScriptClassId(uniqid('Class')));
        });

        test(function () {
            $this->expectException(InvalidTokenException::class);

            $request = new Request;
            $request->setToken(uniqid());

            $this->page->run($request);
        });

        test(function () {
            $this->expectException(InvalidTokenException::class);

            $request = new Request;
            $request->setToken(uniqid());

            $this->page->run($request);
        });

        test(function () {
            $this->body = $this->page->querySelector('body');
            $this->button = $this->body->querySelector('button');

            $this->assertSame($this->page, $this->body->getPage());
            $this->assertSame($this->page, $this->button->getPage());
        });

        test(function () {
            $newControllerUri = uniqid('uri');

            $this->page->setAjaxControllerUri($newControllerUri);

            $this->assertSame($newControllerUri, $this->page->getAjaxControllerUri());
        });

        test(function () {
            $this->assertNotContains('inmutableView', $this->page->__sleep());
        });

        testCase(function () {
            setUp(function () {
                $this->page->setDebug(true);
            });

            test(function () {
                $this->assertTrue($this->page->isDebug());
            });

            testCase(function () {
                setUp(function () {
                    $this->className = uniqid('Class');

                    $this->page->registerJavaScriptClass($this->className);
                });

                test(function () {
                    $this->assertSame(
                        $this->className,
                        $this->page->getJavaScriptClassId($this->className)
                    );
                });
            });
        });

        testCase(function () {
            setUp(function () {
                $this->className = uniqid('Class');

                $this->page->registerJavaScriptClass($this->className);
            });

            test(function () {
                $jsClassId = $this->page->getJavaScriptClassId($this->className);

                $this->assertNotEquals($this->className, $jsClassId);
                $this->assertStringStartsWith('Class', $jsClassId);
                $this->assertEquals(18, strlen($jsClassId));
            });
        });

        testCase(function () {
            setUp(function () {
                $this->page->setBooted(true);
            });

            test(function () {
                $this->assertTrue($this->page->isBooted());
            });
        });

        testCase(function () {
            setUp(function () {
                $this->buttonElement = $this->page->querySelector('button');
            });

            test(function () {
                $this->assertSame($this->buttonElement, $this->page->querySelector('button'));
            });

            test(function () {
                $this->assertTrue($this->page->hasInmutableView());
            });

            test(function () {
                $this->assertSame($this->page, $this->buttonElement->getPage());
            });

            testCase(function () {
                setUp(function () {
                    $this->expectException(InmutableViewException::class);
                });

                test(function () {
                    $this->page->addFilter(function () {
                    });
                });

                test(function () {
                    $child = new class implements ComponentInterface {
                        use ComponentTrait;
                    };

                    $this->page->addChild($child);
                });
            });
        });
    });
});
