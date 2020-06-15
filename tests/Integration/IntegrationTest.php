<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Tests\Integration;

use ThenLabs\StratusPHP\Tests\SeleniumTestCase;
use ThenLabs\StratusPHP\AbstractApp;
use ThenLabs\StratusPHP\StratusEventListener;
use ThenLabs\StratusPHP\Event\StratusEvent;
use Facebook\WebDriver\Remote\RemoteWebElement;

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
                    $label->setStyle('color', 'red');
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
                            <label>MY LABEL</label>
                            <button>Button</button>
                        </body>
                        </html>
                    HTML;
                }

                public function onButtonClick($event): void
                {
                    $label = $event->getApp()->querySelector('label');

                    $label->innerHTML = uniqid();
                    $label->setStyle('color', 'red');
                }
            };

            $button = $app->querySelector('button');
            $label = $app->querySelector('label');

            $button->click(function () use ($label) {
                if ($label->getStyle('color') == 'red') {
                    $label->setStyle('color', 'blue');
                } else {
                    $label->setStyle('color', 'red');
                }
            });

            static::dumpApp($app);
            static::openApp();
        });

        setUp(function () {
            $this->button = static::findElement('button');
        });

        test(function () {
            $this->button->click();
            static::waitForResponse();

            $this->assertEquals(
                'red',
                $this->executeScript('return document.querySelector("label").style.color')
            );
        });

        test(function () {
            $this->button->click();
            static::waitForResponse();

            $this->assertEquals(
                'blue',
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

            $button = $app->querySelector('button');
            $input = $app->querySelector('input');
            $label = $app->querySelector('label');

            $input->registerCriticalProperty('value');

            $listener = new StratusEventListener;
            $listener->setBackListener(function () use ($input, $label) {
                $label->innerHTML = $input->value;
            });

            $button->click($listener);

            static::dumpApp($app);
            static::openApp();
        });

        test(function () {
            $secret1 = uniqid();

            $button = static::findElement('button');
            $label = static::findElement('label');
            $input = static::findElement('input');

            $input->sendKeys($secret1);
            $button->click();
            static::waitForResponse();

            $this->assertEquals($secret1, $label->getText());
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

                $button = $app->querySelector('button');
                $input = $app->querySelector('input');
                $label = $app->querySelector('label');

                $button->addEventListener('click', function () use ($input, $label) {
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
                            <label class="label"></label>
                            <button>Button</button>
                        </body>
                        </html>
                    HTML;
                }
            };

            $button = $app->querySelector('button');
            $label = $app->querySelector('label');

            $button->click(function () use ($label) {
                if ($label->hasClass('label')) {
                    $label->innerHTML = uniqid();
                }
            });

            static::dumpApp($app);
            static::openApp();
        });

        test(function () {
            $button = static::findElement('button');
            $label = static::findElement('label');

            $button->click();
            static::waitForResponse();

            $this->assertNotEmpty($label->getText());
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
                            <label class="label"></label>
                            <button>Button</button>
                        </body>
                        </html>
                    HTML;
                }
            };

            $button = $app->querySelector('button');
            $label = $app->querySelector('label');

            $button->click(function () use ($label) {
                if ($label->hasAttribute('class')) {
                    $label->innerHTML = uniqid();
                }
            });

            static::dumpApp($app);
            static::openApp();
        });

        test(function () {
            $button = static::findElement('button');
            $label = static::findElement('label');

            $button->click();
            static::waitForResponse();

            $this->assertNotEmpty($label->getText());
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
            };

            $button = $app->querySelector('button');
            $label = $app->querySelector('label');

            $button->click(function () use ($label) {
                if (! $label->hasAttribute('class')) {
                    $label->innerHTML = uniqid();
                }
            });

            static::dumpApp($app);
            static::openApp();
        });

        test(function () {
            $button = static::findElement('button');
            $label = static::findElement('label');

            $button->click();
            static::waitForResponse();

            $this->assertNotEmpty($label->getText());
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
            };

            $button = $app->querySelector('button');
            $label = $app->querySelector('label');

            $button->click(function () use ($label) {
                $label->addClass('my-class');
            });

            static::dumpApp($app);
            static::openApp();
        });

        test(function () {
            $button = static::findElement('button');
            $button->click();
            static::waitForResponse();

            $this->assertInstanceOf(RemoteWebElement::class, static::findElement('label.my-class'));
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
                            <label class="label"></label>
                            <button>Button</button>
                        </body>
                        </html>
                    HTML;
                }
            };

            $button = $app->querySelector('button');
            $label = $app->querySelector('label');

            $button->click(function () use ($label) {
                $label->removeClass('label');
            });

            static::dumpApp($app);
            static::openApp();
        });

        test(function () {
            $button = static::findElement('button');
            $button->click();
            static::waitForResponse();

            $this->assertCount(0, static::findElements('label.label'));
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
                            <label class="label"></label>
                            <button>Button</button>
                        </body>
                        </html>
                    HTML;
                }
            };

            $button = $app->querySelector('button');
            $label = $app->querySelector('label');

            $button->click(function () use ($label) {
                $label->removeAttribute('class');
            });

            static::dumpApp($app);
            static::openApp();
        });

        test(function () {
            $button = static::findElement('button');
            $button->click();
            static::waitForResponse();

            $this->assertCount(0, static::findElements('label[class]'));
        });
    });

    testCase(function () {
        createMacro('tests', function () {
            setUp(function () {
                $this->input = static::findElement('input');
                $this->label = static::findElement('label');

                $this->input->clear();
            });

            test(function () {
                $this->input->sendKeys('a');
                static::waitForResponse();

                $this->assertEquals('key: a keyCode: 97', $this->label->getText());
            });

            test(function () {
                $this->input->sendKeys('b');
                static::waitForResponse();

                $this->assertEquals('key: b keyCode: 98', $this->label->getText());
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
                                <label class="label"></label>
                            </body>
                            </html>
                        HTML;
                    }
                };

                $input = $app->querySelector('input');
                $label = $app->querySelector('label');

                $listener = new StratusEventListener;
                $listener->setRequiredEventData(['key', 'keyCode']);
                $listener->setBackListener(function (StratusEvent $event) use ($label): void {
                    $eventData = $event->getEventData();
                    extract($eventData);
                    $label->innerHTML = "key: {$key} keyCode: {$keyCode}";
                });

                $input->addEventListener('keypress', $listener);

                static::dumpApp($app);
                static::openApp();
            });

            useMacro('tests');
        });
    });
});
