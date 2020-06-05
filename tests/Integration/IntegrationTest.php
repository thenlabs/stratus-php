<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Tests\Functionals;

use ThenLabs\StratusPHP\Tests\SeleniumTestCase;
use ThenLabs\StratusPHP\AbstractApp;

setTestCaseNamespace(__NAMESPACE__);
setTestCaseClass(SeleniumTestCase::class);

testCase('IntegrationTest.php', function () {
    testCase(function () {
        createMacro('tests', function () {
            test(function () {
                static::findElement('button')->click();
                static::waitForResponse();

                $this->assertNotEmpty(
                    static::findElement('label')->getText()
                );
            });
        });

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
                                <label></label>
                                <button>Button</button>
                            </body>
                            </html>
                        HTML;
                    }

                    public function listener($event): void
                    {
                        $app = $event->getApp();
                        $label = $app->querySelector('label');

                        $label->innerHTML = uniqid();
                        $label->style = 'color: red';
                    }
                };

                $app->querySelector('button')->click([$app, 'listener']);

                static::dumpApp($app);
                static::openApp();
            });

            useMacro('tests');

            test(function () {
                $this->assertEquals(
                    'red',
                    $this->executeScript('return document.querySelector("label").style.color')
                );
            });
        });

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
                                <label></label>
                                <button>Button</button>
                            </body>
                            </html>
                        HTML;
                    }

                    public function onButtonClick($event): void
                    {
                        $app = $event->getApp();
                        $label = $app->querySelector('label');

                        $label->innerHTML = uniqid();
                    }
                };

                $app->querySelector('button')->click([$app, 'onButtonClick']);

                static::dumpApp($app);
                static::openApp();
            });

            useMacro('tests');
        });
    });

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
                            <label></label>
                            <button>Button</button>
                        </body>
                        </html>
                    HTML;
                }

                public function onButtonClick($event): void
                {
                    $label = $event->getApp()->querySelector('label');

                    $label->innerHTML = uniqid();
                    $label->css('color', 'red');
                }
            };

            $app->querySelector('button')->click([$app, 'onButtonClick']);

            static::dumpApp($app);
            static::openApp();
        });

        test(function () {
            static::findElement('button')->click();
            static::waitForResponse();

            $this->assertEquals(
                'red',
                $this->executeScript('return document.querySelector("label").style.color')
            );
        });
    });

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
                            <input type="" name="">
                            <label></label>
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

            $app->querySelector('input');
            $app->querySelector('label');

            $app->querySelector('button')->click([$app, 'listener']);

            static::dumpApp($app);
            static::openApp();
        });

        setUp(function () {
            $this->input = static::findElement('input');
            $this->button = static::findElement('button');
            $this->label = static::findElement('label');

            $this->input->clear();
        });

        test(function () {
            $secret1 = uniqid();

            $this->input->sendKeys($secret1);
            $this->button->click();
            static::waitForResponse();

            $this->assertEquals($secret1, $this->label->getText());
        });

        test(function () {
            $secret2 = uniqid();

            $this->input->sendKeys($secret2);
            $this->button->click();
            static::waitForResponse();

            $this->assertEquals($secret2, $this->label->getText());
        });
    });

    testCase(function () {
        createMacro('tests', function () {
            setUp(function () {
                $this->input = static::findElement('input');
                $this->button = static::findElement('button');
                $this->label = static::findElement('label');

                $this->input->clear();
            });

            test(function () {
                $secret1 = uniqid();

                $this->input->sendKeys($secret1);
                $this->button->click();
                static::waitForResponse();

                $this->assertEquals($secret1, $this->label->getText());
            });

            test(function () {
                $secret2 = uniqid();

                $this->input->sendKeys($secret2);
                $this->button->click();
                static::waitForResponse();

                $this->assertEquals($secret2, $this->label->getText());
            });
        });

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
                                <input type="" name="">
                                <label></label>
                                <button>Button</button>
                            </body>
                            </html>
                        HTML;
                    }
                };

                $app->querySelector('input');
                $app->querySelector('label');

                $app->querySelector('button')->click(function ($event) {
                    $app = $event->getApp();
                    $input = $app->querySelector('input');
                    $label = $app->querySelector('label');

                    $label->innerHTML = $input->value;
                });

                static::dumpApp($app);
                static::openApp();
            });

            useMacro('tests');
        });

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
                                <input type="" name="">
                                <label></label>
                                <button>Button</button>
                            </body>
                            </html>
                        HTML;
                    }
                };

                $app->querySelector('button')->click(function ($event) {
                    $app = $event->getApp();
                    $input = $app->querySelector('input');
                    $label = $app->querySelector('label');

                    $label->innerHTML = $input->value;
                });

                static::dumpApp($app);
                static::openApp();
            });

            useMacro('tests');
        });

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
                                <input type="" name="">
                                <label></label>
                                <button>Button</button>
                            </body>
                            </html>
                        HTML;
                    }
                };

                $input = $app->querySelector('input');
                $label = $app->querySelector('label');

                $app->querySelector('button')->click(function ($event) use ($input, $label) {
                    $app = $event->getApp();

                    $label->innerHTML = $input->value;
                });

                static::dumpApp($app);
                static::openApp();
            });

            useMacro('tests');
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
                            <title>Document</title>
                        </head>
                        <body>
                            <label></label>
                            <button id="button">Button</button>
                        </body>
                        </html>
                    HTML;
                }
            };

            $app->querySelector('#button')->click(function ($event) use ($attribute, $value) {
                $app = $event->getApp();
                $button = $event->getSource();
                $label = $app->querySelector('label');

                $button->setAttribute($attribute, $value);
                $label->innerHTML = $button->getAttribute($attribute);
            });

            static::dumpApp($app);

            static::addVars(compact('attribute', 'value'));

            static::openApp();
        });

        test(function () {
            $button = static::findElement('button');
            $label = static::findElement('label');

            $button->click();
            static::waitForResponse();

            $this->assertEquals($this->value, $button->getAttribute($this->attribute));
            $this->assertEquals($this->value, $label->getText());
        });
    });
});
