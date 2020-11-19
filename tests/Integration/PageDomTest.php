<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Tests\Integration;

use ThenLabs\StratusPHP\Tests\SeleniumTestCase;
use ThenLabs\StratusPHP\AbstractApp as TestApp;
use ThenLabs\StratusPHP\Plugin\PageDom\Element;
use ThenLabs\StratusPHP\Event\EventListener;
use ThenLabs\StratusPHP\Event\Event;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverExpectedCondition;

setTestCaseNamespace(__NAMESPACE__);
setTestCaseClass(SeleniumTestCase::class);

testCase('PageDomTest.php', function () {
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
                $app = new class('') extends TestApp {
                    use \ThenLabs\StratusPHP\Plugin\PageDom\PageDomTrait;

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

                $app->querySelector('button')->onClick([$app, 'listener']);

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
                $app = new class('') extends TestApp {
                    use \ThenLabs\StratusPHP\Plugin\PageDom\PageDomTrait;

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

                $app->querySelector('button')->onClick([$app, 'onButtonClick']);

                static::dumpApp($app);
                static::openApp();
            });

            useMacro('tests');
        });
    });

    testCase(function () {
        setUpBeforeClassOnce(function () {
            $app = new class('') extends TestApp {
                use \ThenLabs\StratusPHP\Plugin\PageDom\PageDomTrait;

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

            $app->querySelector('button')->onClick([$app, 'onButtonClick']);

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
            $app = new class('') extends TestApp {
                use \ThenLabs\StratusPHP\Plugin\PageDom\PageDomTrait;

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

            $button->onClick(function () use ($label) {
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
            $app = new class('') extends TestApp {
                use \ThenLabs\StratusPHP\Plugin\PageDom\PageDomTrait;

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

            $app->querySelector('button')->onClick([$app, 'listener']);

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
            $app = new class('') extends TestApp {
                use \ThenLabs\StratusPHP\Plugin\PageDom\PageDomTrait;

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

            $listener = new EventListener;
            $listener->setBackListener(function () use ($input, $label) {
                $label->innerHTML = $input->value;
            });

            $button->onClick($listener);

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
                $app = new class('') extends TestApp {
                    use \ThenLabs\StratusPHP\Plugin\PageDom\PageDomTrait;

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

                $app->querySelector('button')->onClick(function ($event) {
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
                $app = new class('') extends TestApp {
                    use \ThenLabs\StratusPHP\Plugin\PageDom\PageDomTrait;

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

                $app->querySelector('button')->onClick(function ($event) {
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
                $app = new class('') extends TestApp {
                    use \ThenLabs\StratusPHP\Plugin\PageDom\PageDomTrait;

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

                $app->querySelector('button')->onClick(function ($event) use ($input, $label) {
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
                $app = new class('') extends TestApp {
                    use \ThenLabs\StratusPHP\Plugin\PageDom\PageDomTrait;

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

            $app = new class('') extends TestApp {
                use \ThenLabs\StratusPHP\Plugin\PageDom\PageDomTrait;

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

            $app->querySelector('#button')->onClick(function ($event) use ($attribute, $value) {
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
            $app = new class('') extends TestApp {
                use \ThenLabs\StratusPHP\Plugin\PageDom\PageDomTrait;

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

            $button->onClick(function () use ($label) {
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
            $app = new class('') extends TestApp {
                use \ThenLabs\StratusPHP\Plugin\PageDom\PageDomTrait;

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

            $button->onClick(function () use ($label) {
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
            $app = new class('') extends TestApp {
                use \ThenLabs\StratusPHP\Plugin\PageDom\PageDomTrait;

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

            $button->onClick(function () use ($label) {
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
            $app = new class('') extends TestApp {
                use \ThenLabs\StratusPHP\Plugin\PageDom\PageDomTrait;

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

            $button->onClick(function () use ($label) {
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
            $app = new class('') extends TestApp {
                use \ThenLabs\StratusPHP\Plugin\PageDom\PageDomTrait;

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

            $button->onClick(function () use ($label) {
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
            $app = new class('') extends TestApp {
                use \ThenLabs\StratusPHP\Plugin\PageDom\PageDomTrait;

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

            $button->onClick(function () use ($label) {
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
                $app = new class('') extends TestApp {
                    use \ThenLabs\StratusPHP\Plugin\PageDom\PageDomTrait;

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

                $listener = new EventListener;
                $listener->setFetchData(['key', 'keyCode']);
                $listener->setBackListener(function (Event $event) use ($label): void {
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

    testCase(function () {
        setUpBeforeClassOnce(function () {
            $app = new class('') extends TestApp {
                use \ThenLabs\StratusPHP\Plugin\PageDom\PageDomTrait;

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

            $listener = new EventListener;
            $listener->setFrontListener(<<<JAVASCRIPT
                let label = document.querySelector('label');
                label.remove();
            JAVASCRIPT);

            $button->addEventListener('click', $listener);

            static::dumpApp($app);
            static::openApp();
        });

        test(function () {
            $button = static::findElement('button');
            $button->click();
            static::waitForResponse();

            $this->assertCount(0, static::findElements('label'));
        });
    });

    testCase(function () {
        setUpBeforeClassOnce(function () {
            $app = new class('') extends TestApp {
                use \ThenLabs\StratusPHP\Plugin\PageDom\PageDomTrait;

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

            $label = $app->querySelector('label');
            $button = $app->querySelector('button');

            $listener = new EventListener;

            $listener->setFrontListener(<<<JAVASCRIPT
                event.backListener = false;
            JAVASCRIPT);

            $listener->setBackListener(function () use ($label) {
                $label->innerHTML = uniqid();
            });

            $button->addEventListener('click', $listener);

            static::dumpApp($app);
            static::openApp();
        });

        test(function () {
            $button = static::findElement('button');
            $button->click();
            static::waitForResponse();

            $label = static::findElement('label');

            $this->assertEmpty($label->getText());
        });
    });

    testCase(function () {
        setUpBeforeClassOnce(function () {
            $app = new class('') extends TestApp {
                use \ThenLabs\StratusPHP\Plugin\PageDom\PageDomTrait;

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
                            <label></label>
                            <button>Button</button>
                        </body>
                        </html>
                    HTML;
                }
            };

            $label = $app->querySelector('label');
            $input = $app->querySelector('input');

            $input->addEventListener('keypress', [
                'fetchData' => ['key', 'keyCode'],

                'frontListener' => <<<JAVASCRIPT
                    let label = document.querySelector('label');
                    label.innerHTML = `key: \${event.key} `;
                JAVASCRIPT,

                'backListener' => function (Event $event) use ($label): void {
                    $app = $event->getApp();
                    $label = $app->querySelector('label');

                    $eventData = $event->getEventData();

                    $label->innerHTML .= " keyCode: {$eventData['keyCode']}";
                },
            ]);

            static::dumpApp($app);
            static::openApp();
        });

        test(function () {
            $input = static::findElement('input');
            $input->sendKeys('a');
            static::waitForResponse();

            $label = static::findElement('label');

            $this->assertEquals('key: a keyCode: 97', $label->getText());
        });
    });

    testCase(function () {
        setUpBeforeClassOnce(function () {
            $app = new class('') extends TestApp {
                use \ThenLabs\StratusPHP\Plugin\PageDom\PageDomTrait;

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
                            <button>Button</button>
                        </body>
                        </html>
                    HTML;
                }
            };

            $button = $app->querySelector('button');

            $input = $app->querySelector('input');
            $input->setId('input');

            $button->onClick(function () use ($input) {
                $input->remove();
            });

            static::dumpApp($app);
            static::openApp();
        });

        test(function () {
            $button = static::findElement('button');
            $button->click();
            static::waitForResponse();

            $this->assertCount(0, static::findElements('input'));
            $this->assertTrue(
                $this->executeScript("return stratusAppInstance.getComponent('input') == null")
            );
        });
    });

    testCase(function () {
        setUpBeforeClassOnce(function () {
            $app = new class('') extends TestApp {
                use \ThenLabs\StratusPHP\Plugin\PageDom\PageDomTrait;

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
                            <button id="button1">1</button>
                            <button id="button2">2</button>
                            <button id="button3">3</button>
                            <label></label>
                        </body>
                        </html>
                    HTML;
                }
            };

            $label = $app->querySelector('label');
            $label->setId('label');

            $body = $app->querySelector('body');
            $body->setId('body');

            $body->addEventListener('click', function ($event) use ($label) {
                $button = $event->getTarget();
                $label->innerHTML = $button->innerHTML;
            }, true);

            static::dumpApp($app);
            static::openApp();
        });

        test(function () {
            $number = mt_rand(1, 3);
            $button = static::findElement("#button{$number}");
            $button->click();
            static::waitForResponse();

            $label = static::findElement('label');

            $this->assertEquals($number, $label->getText());
        });
    });

    testCase(function () {
        setUpBeforeClassOnce(function () {
            $app = new class('') extends TestApp {
                use \ThenLabs\StratusPHP\Plugin\PageDom\PageDomTrait;

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
                            <button>redirect to about:blank</button>
                        </body>
                        </html>
                    HTML;
                }
            };

            $button = $app->querySelector('button');
            $button->onClick(function ($event) {
                $app = $event->getApp();
                $app->redirect('about:blank');
            });

            static::dumpApp($app);
            static::openApp();
        });

        test(function () {
            $button = static::findElement('button');
            $button->click();
            usleep(100000);

            $this->assertEquals('about:blank', static::getDriver()->getCurrentURL());
        });
    });

    testCase(function () {
        setUpBeforeClassOnce(function () {
            $app = new class('') extends TestApp {
                use \ThenLabs\StratusPHP\Plugin\PageDom\PageDomTrait;

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
            $button->onClick(function ($event) {
                $app = $event->getApp();
                $input = $app->querySelector('input');
                $label = $app->querySelector('label');

                $label->textContent = $input->value;
            });

            static::dumpApp($app);
            static::openApp();
        });

        test(function () {
            $value = uniqid();
            $input = static::findElement('input');
            $input->sendKeys($value);

            $label = static::findElement('label');

            $button = static::findElement('button');
            $button->click();
            static::waitForResponse();

            $this->assertEquals($value, $label->getText());
        });
    });

    testCase(function () {
        setUpBeforeClassOnce(function () {
            $app = new class('') extends TestApp {
                use \ThenLabs\StratusPHP\Plugin\PageDom\PageDomTrait;

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
                            <div class="container">
                                <button>Button</button>
                            </div>
                        </body>
                        </html>
                    HTML;
                }
            };

            $app->querySelector('button')->addEventListener('click', function ($event) {
                $app = $event->getApp();
                $container = $app->querySelector('.container');

                $input = Element::createFromString('<input type="" name="">');
                $label = Element::createFromString('<label></label>');

                $container->append($input);
                $container->prepend($label);
            });

            static::dumpApp($app);
            static::openApp();
        });

        test(function () {
            $button = static::findElement('button');
            $button->click();
            static::waitForResponse();

            $childs = static::findElements('div.container > *');

            $this->assertCount(3, $childs);
            $this->assertEquals('label', $childs[0]->getTagName());
            $this->assertEquals('button', $childs[1]->getTagName());
            $this->assertEquals('input', $childs[2]->getTagName());
        });
    });

    testCase(function () {
        setUpBeforeClassOnce(function () {
            $app = new class('') extends TestApp {
                use \ThenLabs\StratusPHP\Plugin\PageDom\PageDomTrait;

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
                            <button>Button</button>
                        </body>
                        </html>
                    HTML;
                }
            };

            $app->on('click', function ($event) {
                $app = $event->getApp();
                $app->getBrowser()->alert('Clicked');
            });

            static::dumpApp($app);
            static::openApp();
        });

        test(function () {
            static::executeScript('stratusAppInstance.dispatch("click", {}, false);');

            $driver = static::getDriver();

            $driver->wait()->until(
                WebDriverExpectedCondition::alertIsPresent(),
                'Clicked'
            );
            $driver->switchTo()->alert()->accept();

            $this->assertTrue(true);
        });
    });

    testCase(function () {
        setUpBeforeClassOnce(function () {
            $app = new class('') extends TestApp {
                use \ThenLabs\StratusPHP\Plugin\PageDom\PageDomTrait;

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
                            <div class="container">
                            </div>
                            <button>Button</button>
                        </body>
                        </html>
                    HTML;
                }
            };

            $button = $app->querySelector('button');
            $div = $app->querySelector('div.container');

            $button->onClick(function () use ($div) {
                $div->innerHTML = <<<HTML
                    <input type="" name="">
                    <label></label>
                HTML;
            });

            static::dumpApp($app);
            static::openApp();
        });

        test(function () {
            static::findElement('button')->click();
            static::waitForResponse();

            $this->assertCount(1, static::findElements('input'));
            $this->assertCount(1, static::findElements('label'));
        });
    });

    // testCase(function () {
    //     setUpBeforeClassOnce(function () {
    //         $app = new class('') extends TestApp {
    //             public function getView(): string
    //             {
    //                 return <<<HTML
    //                     <!DOCTYPE html>
    //                     <html lang="en">
    //                     <head>
    //                         <meta charset="UTF-8">
    //                         <title>Document</title>
    //                     </head>
    //                     <body>
    //                         <div class="container">
    //                             <button>Button</button>
    //                         </div>
    //                     </body>
    //                     </html>
    //                 HTML;
    //             }
    //         };

    //         $container = $app->querySelector('.container');
    //         $button = $container->querySelector('button');

    //         $button->addEventListener('click', function ($event) {
    //             $container = $event->getApp()->querySelector('.container');

    //             $input = Element::createFromString('<input type="text" name="" value="abcd123">');
    //             $container->append($input);

    //             $label = Element::createFromString('<label></label>');
    //             $container->append($label);

    //             $label->textContent = $input->value;
    //         });

    //         static::dumpApp($app);
    //         static::openApp();
    //     });

    //     test(function () {
    //         $button = static::findElement('button');
    //         $button->click();
    //         static::waitForResponse();

    //         $label = static::findElement('label');

    //         $this->assertEquals('abcd123', $label->getText());
    //     });
    // });

    // testCase(function () {
    //     setUpBeforeClassOnce(function () {
    //         $app = new class('') extends TestApp {
    //             public function getView(): string
    //             {
    //                 return <<<HTML
    //                     <!DOCTYPE html>
    //                     <html lang="en">
    //                     <head>
    //                         <meta charset="UTF-8">
    //                         <title>Document</title>
    //                     </head>
    //                     <body>
    //                         <div class="container">
    //                             <button>Button</button>
    //                         </div>
    //                     </body>
    //                     </html>
    //                 HTML;
    //             }
    //         };

    //         $container = $app->querySelector('.container');
    //         $button = $container->querySelector('button');

    //         $button->addEventListener('click', function ($event) use ($container) bug {
    //             $container = $event->getApp()->querySelector('.container');

    //             if (! $input = $container->querySelector('input')) {
    //                 $input = Element::createFromString('<input type="text" name="" value="abcd123">');
    //                 $container->append($input);
    //             }

    //             if (! $label = $container->querySelector('label')) {
    //                 $label = Element::createFromString('<label></label>');
    //                 $container->append($label);
    //             }

    //             $label->textContent = $input->value;
    //         });

    //         static::dumpApp($app);
    //         static::openApp();
    //     });

    //     test(function () {
    //         $button = static::findElement('button');
    //         $button->click();
    //         static::waitForResponse();

    //         $label = static::findElement('label');

    //         $this->assertEquals('abcd123', $label->getText());
    //     });
    // });
});
