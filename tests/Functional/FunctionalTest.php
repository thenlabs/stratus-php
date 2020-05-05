<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Tests\Functionals;

use ThenLabs\StratusPHP\Tests\SeleniumTestCase;
use ThenLabs\StratusPHP\JavaScript\JavaScriptClassInterface;
use ThenLabs\StratusPHP\JavaScript\JavaScriptInstanceInterface;
use ThenLabs\StratusPHP\AbstractApp;
use ThenLabs\ComposedViews\AbstractCompositeView;
use ThenLabs\ClassBuilder\ClassBuilder;

setTestCaseNamespace(__NAMESPACE__);
setTestCaseClass(SeleniumTestCase::class);

testCase('FunctionalTest.php', function () {
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
            $className1 = uniqid('Class1_');
            $className2 = uniqid('Class2_');
            $className3 = uniqid('Class3_');
            $className4 = uniqid('Class4_');
            $className5 = uniqid('Class5_');
            $className6 = uniqid('Class6_');

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

            $app->setDebug(true);

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
                ->extends($className1)
                ->implements(JavaScriptInstanceInterface::class)
                ->addMethod('getView', function (): string {
                    return '';
                })->end()
                ->addMethod('getJavaScriptClassMembers')
                    ->setStatic(true)
                    ->setClosure(function (): string {
                        return <<<JAVASCRIPT
                            constructor (secret) {
                                super();
                                this.secret = secret;
                            }

                            getValue3() {
                                return 3;
                            }
                        JAVASCRIPT;
                    })
                ->end()
                ->addMethod('getJavaScriptCreateInstanceScript')
                    ->setClosure(function () use ($secret): string {
                        return <<<JAVASCRIPT
                            window.child3 = new ComponentClass('{$secret}');
                        JAVASCRIPT;
                    })
                ->end()
                ->newInstance()
            ;

            $class6 = (new ClassBuilder($className6))
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
                            getValue6() { return 6 }
                        JAVASCRIPT;
                    })
                ->end()
                ->install()
            ;

            $class5 = (new ClassBuilder($className5))
                ->setNamespace($namespace)
                ->extends($class6->getFCQN())
                ->implements(JavaScriptClassInterface::class)
                ->addMethod('getView', function (): string {
                    return '';
                })->end()
                ->addMethod('getJavaScriptClassMembers')
                    ->setStatic(true)
                    ->setClosure(function (): string {
                        return <<<JAVASCRIPT
                            getValue5() { return 5 }
                        JAVASCRIPT;
                    })
                ->end()
                ->install()
            ;

            $child4 = (new ClassBuilder($className4))
                ->setNamespace($namespace)
                ->extends($class5->getFCQN())
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
                'className5',
                'className6',
                'namespace',
                'secret',
            ));

            static::openApp();

            static::setVar('fcqn1', $className1);
            static::setVar('fcqn2', "{$namespace}\\$className2");
            static::setVar('fcqn3', "{$namespace}\\$className3");
            static::setVar('fcqn4', "{$namespace}\\$className4");
            static::setVar('fcqn5', "{$namespace}\\$className5");
            static::setVar('fcqn6', "{$namespace}\\$className6");
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

        test(function () {
            $script = <<<JAVASCRIPT
                let Class1 = stratusAppInstance.getClass('{$this->fcqn1}');
                return {
                    isInstance: child3 instanceof Class1,
                    result1: child3.getValue1(),
                    result3: child3.getValue3(),
                };
            JAVASCRIPT;

            $result = static::executeScript($script);

            $this->assertTrue($result['isInstance']);
            $this->assertEquals(1, $result['result1']);
            $this->assertEquals(3, $result['result3']);
        });

        test(function () {
            $script = <<<JAVASCRIPT
                let Class4 = stratusAppInstance.getClass('{$this->fcqn4}');
                let Class5 = stratusAppInstance.getClass('{$this->fcqn5}');
                let Class6 = stratusAppInstance.getClass('{$this->fcqn6}');

                let child4 = new Class4();

                return {
                    isInstance5: child4 instanceof Class5,
                    isInstance6: child4 instanceof Class6,
                    result4: child4.getValue4(),
                    result5: child4.getValue5(),
                    result6: child4.getValue6(),
                };
            JAVASCRIPT;

            $result = static::executeScript($script);

            $this->assertTrue($result['isInstance5']);
            $this->assertTrue($result['isInstance6']);
            $this->assertEquals(4, $result['result4']);
            $this->assertEquals(5, $result['result5']);
            $this->assertEquals(6, $result['result6']);
        });
    });

    testCase(function () {
        setUpBeforeClassOnce(function () {
            $className1 = uniqid('Class1_');
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

            $app->addChild($child1);

            static::dumpApp($app);

            static::addVars(compact(
                'className1',
                'secret',
            ));

            static::openApp();

            static::setVar('jsClassId1', $app->getJavaScriptClassId($className1));
        });

        test(function () {
            $script = <<<JAVASCRIPT
                let c = stratusAppInstance.getClass('{$this->jsClassId1}');
                let instance = new c;
                return instance.getValue1();
            JAVASCRIPT;

            $this->assertEquals(1, static::executeScript($script));
        });
    });

    testCase(function () {
        setUpBeforeClassOnce(function () {
            $attribute = uniqid('attr-');
            $value = uniqid();

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

            $app->querySelector('body')->setAttribute($attribute, $value);

            static::dumpApp($app);

            static::addVars(compact('attribute', 'value'));

            static::openApp();
        });

        test(function () {
            $script = <<<JAVASCRIPT
                return document.querySelector('body').getAttribute('{$this->attribute}');
            JAVASCRIPT;

            $this->assertEquals($this->value, static::executeScript($script));
        });
    });

    testCase(function () {
        setUpBeforeClassOnce(function () {
            $attribute1 = uniqid('attr-');
            $attribute2 = uniqid('attr-');
            $value1 = uniqid();
            $value2 = uniqid();

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
                            <div class="container">
                                <div class="row">
                                    <div class="col"></div>
                                    <div class="col"></div>
                                    <div class="col"></div>
                                </div>
                            </div>
                        </body>
                        </html>
                    HTML;
                }
            };

            $container = $app->querySelector('.container');
            $container->setAttribute($attribute1, $value1);

            $firstCol = $container->querySelector('.col');
            $firstCol->setAttribute($attribute2, $value2);

            static::dumpApp($app);

            static::addVars(compact('attribute1', 'value1', 'attribute2', 'value2'));

            static::openApp();
        });

        test(function () {
            $script = <<<JAVASCRIPT
                return document.querySelector('.container').getAttribute('{$this->attribute1}');
            JAVASCRIPT;

            $this->assertEquals($this->value1, static::executeScript($script));
        });

        test(function () {
            $script = <<<JAVASCRIPT
                return document.querySelector('.col').getAttribute('{$this->attribute2}');
            JAVASCRIPT;

            $this->assertEquals($this->value2, static::executeScript($script));
        });
    });

    testCase('temp', function () {
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

                public function listener($event): void
                {
                    $app = $event->getApp();
                    $input = $app->querySelector('input');
                    $label = $app->querySelector('label');

                    $label->innerHTML = $input->value;
                }
            };

            $app->querySelector('button')->click([$app, 'listener']);

            static::dumpApp($app);
            static::openApp();
        });

        test(function () {
            $this->assertTrue(static::executeScript('return stratusAppInstance instanceof StratusApp'));
        });

        test(function () {
            $secret = uniqid();

            static::findElement('input')->sendKeys($secret);
            static::findElement('button')->click();

            $this->assertContains(
                $secret,
                static::findElement('label')->getText()
            );
        });
    });
});
