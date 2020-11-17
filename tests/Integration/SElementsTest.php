<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Tests\Integration;

use ThenLabs\StratusPHP\Tests\SeleniumTestCase;
use ThenLabs\StratusPHP\AbstractApp as TestApp;
use ThenLabs\StratusPHP\Annotation\EventListener;

setTestCaseNamespace(__NAMESPACE__);
setTestCaseClass(SeleniumTestCase::class);

testCase('SElementsTest.php', function () {
    testCase(function () {
        setUpBeforeClassOnce(function () {
            $app = new class('') extends TestApp {
                use \ThenLabs\StratusPHP\Plugin\PageDom\PageDomTrait;
                use \ThenLabs\StratusPHP\Plugin\SElements\SElementsTrait;

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
                            <input s-element="myInput" type="text" name="">
                            <label s-element="myLabel"></label>
                            <button s-element="myButton">MyButton</button>
                        </body>
                        </html>
                    HTML;
                }

                public function onClickMyButton()
                {
                    $this->myLabel->innerHTML = $this->myInput->value;
                }
            };

            static::dumpApp($app);
            static::openApp();
        });

        test(function () {
            $input = static::findElement('input');
            $button = static::findElement('button');
            $label = static::findElement('label');

            $secret = uniqid();

            $input->sendKeys($secret);
            $button->click();
            static::waitForResponse();

            $this->assertEquals($secret, $label->getText());
        });
    });

    testCase(function () {
        setUpBeforeClassOnce(function () {
            $app = new class('') extends TestApp {
                use \ThenLabs\StratusPHP\Plugin\PageDom\PageDomTrait;
                use \ThenLabs\StratusPHP\Plugin\SElements\SElementsTrait;

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
                            <input s-element="myInput" type="text" name="">
                            <label s-element="myLabel"></label>
                            <button s-element="myButton" s-element-event-click="clickTheButton">MyButton</button>
                        </body>
                        </html>
                    HTML;
                }

                public function clickTheButton()
                {
                    $this->myLabel->innerHTML = $this->myInput->value;
                }
            };

            static::dumpApp($app);
            static::openApp();
        });

        test(function () {
            $input = static::findElement('input');
            $button = static::findElement('button');
            $label = static::findElement('label');

            $secret = uniqid();

            $input->sendKeys($secret);
            $button->click();
            static::waitForResponse();

            $this->assertEquals($secret, $label->getText());
        });
    });

    testCase(function () {
        setUpBeforeClassOnce(function () {
            $app = new class('') extends TestApp {
                use \ThenLabs\StratusPHP\Plugin\PageDom\PageDomTrait;
                use \ThenLabs\StratusPHP\Plugin\SElements\SElementsTrait;

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
                            <input s-element="myInput" type="text" name="">
                            <label s-element="myLabel"></label>
                            <button s-element="myButton">MyButton</button>
                        </body>
                        </html>
                    HTML;
                }

                public function onClickMyButton()
                {
                    if (empty($this->myInput->value)) {
                        $this->myLabel->innerHTML = 'The input is empty';
                    } else {
                        $this->myLabel->innerHTML = 'The input is not empty';
                    }
                }
            };

            static::dumpApp($app);
            static::openApp();
        });

        test(function () {
            $input = static::findElement('input');
            $button = static::findElement('button');
            $label = static::findElement('label');

            $button->click();
            static::waitForResponse();

            $this->assertEquals('The input is empty', $label->getText());

            $input->sendKeys(uniqid());

            $button->click();
            static::waitForResponse();

            $this->assertEquals('The input is not empty', $label->getText());
        });
    });

    testCase(function () {
        setUpBeforeClassOnce(function () {
            $app = new class('') extends TestApp {
                use \ThenLabs\StratusPHP\Plugin\PageDom\PageDomTrait;
                use \ThenLabs\StratusPHP\Plugin\SElements\SElementsTrait;

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
                            <input s-element="myInput" type="text" name="">
                            <label s-element="myLabel"></label>
                            <button s-element="myButton">MyButton</button>
                        </body>
                        </html>
                    HTML;
                }

                /**
                 * @EventListener(
                 *     frontListener="myInput.setAttribute('disabled', true)"
                 * )
                 */
                public function onClickMyButton()
                {
                    if (true == $this->myInput->getAttribute('disabled')) {
                        $this->myLabel->innerHTML = 'OK';
                    }
                }
            };

            static::dumpApp($app);
            static::openApp();
        });

        test(function () {
            $input = static::findElement('input');
            $button = static::findElement('button');
            $label = static::findElement('label');

            $button->click();
            static::waitForResponse();

            $this->assertEquals('OK', $label->getText());
        });
    });

    testCase(function () {
        setUpBeforeClassOnce(function () {
            $app = new class('') extends TestApp {
                use \ThenLabs\StratusPHP\Plugin\PageDom\PageDomTrait;
                use \ThenLabs\StratusPHP\Plugin\SElements\SElementsTrait;

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
                            <input s-element="myInput" type="text" name="">
                            <label s-element="myLabel"></label>
                            <button s-element="myButton">MyButton</button>
                        </body>
                        </html>
                    HTML;
                }

                /**
                 * @EventListener(
                 *     frontListener="
                 *         myInput.setAttribute('disabled', true);
                 *         myLabel.setAttribute('disabled', true);
                 *         myButton.setAttribute('disabled', true);
                 *     "
                 * )
                 */
                public function onClickMyButton()
                {
                    if (true == $this->myInput->getAttribute('disabled') &&
                        true == $this->myLabel->getAttribute('disabled') &&
                        true == $this->myButton->getAttribute('disabled')
                    ) {
                        $this->myLabel->innerHTML = 'OK';
                    }
                }
            };

            static::dumpApp($app);
            static::openApp();
        });

        test(function () {
            $input = static::findElement('input');
            $button = static::findElement('button');
            $label = static::findElement('label');

            $button->click();
            static::waitForResponse();

            $this->assertEquals('OK', $label->getText());
        });
    });

    testCase(function () {
        setUpBeforeClassOnce(function () {
            $app = new class('') extends TestApp {
                use \ThenLabs\StratusPHP\Plugin\PageDom\PageDomTrait;
                use \ThenLabs\StratusPHP\Plugin\SElements\SElementsTrait;

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
                            <input s-element="myInput" type="text" name="">
                            <label s-element="myLabel"></label>
                            <button s-element="myButton">MyButton</button>
                        </body>
                        </html>
                    HTML;
                }

                public function myFrontListener(): string
                {
                    return <<<JAVASCRIPT
                        myInput.setAttribute('disabled', true);
                        myLabel.setAttribute('disabled', true);
                        myButton.setAttribute('disabled', true);
                    JAVASCRIPT;
                }

                /**
                 * @EventListener(
                 *     frontListener="myFrontListener"
                 * )
                 */
                public function onClickMyButton()
                {
                    if (true == $this->myInput->getAttribute('disabled') &&
                        true == $this->myLabel->getAttribute('disabled')
                    ) {
                        $this->myLabel->innerHTML = 'OK';
                    }
                }
            };

            static::dumpApp($app);
            static::openApp();
        });

        test(function () {
            $input = static::findElement('input');
            $button = static::findElement('button');
            $label = static::findElement('label');

            $button->click();
            static::waitForResponse();

            $this->assertEquals('OK', $label->getText());
        });
    });

    testCase(function () {
        setUpBeforeClassOnce(function () {
            $app = new class('') extends TestApp {
                use \ThenLabs\StratusPHP\Plugin\PageDom\PageDomTrait;
                use \ThenLabs\StratusPHP\Plugin\SElements\SElementsTrait;

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
                            <input s-element="myInput" type="text" name="">
                            <label s-element="myLabel"></label>
                        </body>
                        </html>
                    HTML;
                }

                /**
                 * @EventListener(fetchData={"key", "keyCode"})
                 */
                public function onKeypressMyInput($event)
                {
                    $eventData = $event->getEventData();
                    extract($eventData);
                    $this->myLabel->textContent = "key: {$key} keyCode: {$keyCode}";
                }
            };

            static::dumpApp($app);
            static::openApp();
        });

        test(function () {
            $input = static::findElement('input');
            $label = static::findElement('label');

            $input->sendKeys('a');
            static::waitForResponse();

            $this->assertEquals('key: a keyCode: 97', $label->getText());
        });
    });
});
