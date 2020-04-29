<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Tests;

use ThenLabs\StratusPHP\JavaScript\JavaScriptClassInterface;
use ThenLabs\StratusPHP\JavaScript\JavaScriptInstanceInterface;
use ThenLabs\StratusPHP\AbstractApp;
use ThenLabs\ComposedViews\AbstractCompositeView;
use ThenLabs\ClassBuilder\ClassBuilder;

setTestCaseNamespace(__NAMESPACE__);
setTestCaseClass(SeleniumTestCase::class);

testCase('IntegrationTest.php', function () {
    testCase(function () {
        setUpBeforeClassOnce(function () {
            $app = new class('') extends AbstractApp {
                public function getView(): string
                {
                    return <<<HTML
                        <!DOCTYPE html>
                        <html lang="en">
                        <head>
                            <meta charset="UTF-8">
                            <title>Document</title>
                        </head>
                        <body>
                            <input type="text" name="">
                            <label>label</label>
                            <button>Button</button>
                        </body>
                        </html>
                    HTML;
                }
            };

            $input = $app->filter('input');
            $label = $app->filter('label');
            $button = $app->filter('button');

            $button->click(function () use ($input, $label) {
                $label->setInnerHtml($input->value);
            });

            static::dumpApp($app);
            static::openApp();
        });

        test(function () {
            $this->assertTrue(static::executeScript('return stratusAppInstance instanceof StratusApp'));
        });

        // test(function () {
        //     $secret = uniqid();

        //     static::findElement('input')->sendKeys($secret);
        //     static::findElement('button')->click();

        //     $this->assertContains(
        //         $secret,
        //         static::findElement('label')->getText()
        //     );
        // });
    });

    testCase(function () {
        setUpBeforeClassOnce(function () {
            $jsVarName = uniqid('app');

            $app = new class('') extends AbstractApp {
                public function getView(): string
                {
                    return <<<HTML
                        <html>
                            <head>
                            </head>
                            <body>
                            </body>
                        </html>
                    HTML;
                }
            };

            $app->setJSVarName($jsVarName);
            static::dumpApp($app);

            static::setVar('jsVarName', $jsVarName);
            static::openApp();
        });

        test(function () {
            $this->assertTrue(static::executeScript("return {$this->jsVarName} instanceof StratusApp"));
        });
    });

    testCase(function () {
        setUpBeforeClassOnce(function () {
            $className1 = uniqid('Class');
            $className2 = uniqid('Class');
            $className3 = uniqid('Class');
            $className4 = uniqid('Class');

            $namespace = uniqid('Namespace');

            $secret = uniqid();

            $app = new class('') extends AbstractApp {
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
                        </body>
                        </html>
                    HTML;
                }
            };

            $child1 = (new ClassBuilder($className1))
                ->extends(AbstractCompositeView::class)
                ->implements(JavaScriptClassInterface::class)
                ->addMethod('getView', function (): string {
                    return '';
                })->end()
                ->addMethod('getJavaScriptClassMembers')
                    ->setStatic(true)
                    ->setClosure(function (): string {
                        return <<<JAVASCRIPT
                            getValue1() { return 1 }
                        JAVASCRIPT;
                    })
                ->end()
                ->newInstance()
            ;

            $child2 = (new ClassBuilder($className2))
                ->setNamespace($namespace)
                ->extends(AbstractCompositeView::class)
                ->addMethod('getView', function (): string {
                    return '';
                })->end()
                ->newInstance()
            ;

            $child3 = (new ClassBuilder($className3))
                ->setNamespace($namespace)
                ->extends(AbstractCompositeView::class)
                ->implements(JavaScriptInstanceInterface::class)
                ->addMethod('getView', function (): string {
                    return '';
                })->end()
                ->addMethod('getJavaScriptClassMembers')
                    ->setStatic(true)
                    ->setClosure(function (): string {
                        return <<<JAVASCRIPT
                            constructor (secret) {
                                this.secret = secret;
                            }

                            getValue3() {
                                return 3;
                            }
                        JAVASCRIPT;
                    })
                ->end()
                ->addMethod('getJavaScriptCreateInstance')
                    ->setClosure(function () use ($secret): string {
                        return <<<JAVASCRIPT
                            window.child3 = new ComponentClass('{$secret}');
                        JAVASCRIPT;
                    })
                ->end()
                ->newInstance()
            ;

            $child4 = (new ClassBuilder($className4))
                ->setNamespace($namespace)
                ->extends(AbstractCompositeView::class)
                ->implements(JavaScriptClassInterface::class)
                ->addMethod('getView', function (): string {
                    return '';
                })->end()
                ->addMethod('getJavaScriptClassMembers')
                    ->setStatic(true)
                    ->setClosure(function (): string {
                        return <<<JAVASCRIPT
                            getValue4() { return 4 }
                        JAVASCRIPT;
                    })
                ->end()
                ->newInstance()
            ;

            $child1->addChild($child2);
            $child2->addChild($child3);

            $app->addChild($child1);
            $app->addChild($child4);

            static::dumpApp($app);

            static::addVars(compact(
                'className1',
                'className2',
                'className3',
                'className4',
                'namespace',
                'secret',
            ));

            static::openApp();

            static::setVar('fcqn1', $className1);
            static::setVar('fcqn2', "{$namespace}\\$className2");
            static::setVar('fcqn3', "{$namespace}\\$className3");
            static::setVar('fcqn4', "{$namespace}\\$className4");
        });

        test(function () {
            $script = <<<JAVASCRIPT
                let c = stratusAppInstance.getClass('{$this->fcqn1}');
                let instance = new c;
                return instance.getValue1();
            JAVASCRIPT;

            $this->assertEquals(1, static::executeScript($script));
        });

        test(function () {
            $script = <<<JAVASCRIPT
                let c = stratusAppInstance.getClass('{$this->fcqn2}');
                return c;
            JAVASCRIPT;

            $this->assertNull(static::executeScript($script));
        });

        test(function () {
            $script = <<<JAVASCRIPT
                let c = stratusAppInstance.getClass('{$this->fcqn3}');
                let instance = new c;
                return instance.getValue3();
            JAVASCRIPT;

            $this->assertEquals(3, static::executeScript($script));
        });

        test(function () {
            $script = <<<JAVASCRIPT
                let c = stratusAppInstance.getClass('{$this->fcqn4}');
                let instance = new c;
                return instance.getValue4();
            JAVASCRIPT;

            $this->assertEquals(4, static::executeScript($script));
        });

        test(function () {
            $script = <<<JAVASCRIPT
                return window.child3.secret;
            JAVASCRIPT;

            $this->assertEquals($this->secret, static::executeScript($script));
        });
    });
});
