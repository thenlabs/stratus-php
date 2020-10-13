<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Tests\Integration;

use ThenLabs\StratusPHP\Tests\SeleniumTestCase;
use ThenLabs\StratusPHP\AbstractSugarApp as TestApp;
use ThenLabs\StratusPHP\Annotation\StratusEventListener;

setTestCaseNamespace(__NAMESPACE__);
setTestCaseClass(SeleniumTestCase::class);

testCase('SugarIntegrationTest.php', function () {
    testCase(function () {
        setUpBeforeClassOnce(function () {
            $app = new class('') extends TestApp {
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
                            <input s-elem="myInput" type="text" name="">
                            <label s-elem="myLabel"></label>
                            <button s-elem="myButton">MyButton</button>
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
                            <input s-elem="myInput" type="text" name="">
                            <label s-elem="myLabel"></label>
                            <button s-elem="myButton">MyButton</button>
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
                            <input s-elem="myInput" type="text" name="">
                            <label s-elem="myLabel"></label>
                            <button s-elem="myButton">MyButton</button>
                        </body>
                        </html>
                    HTML;
                }

                /**
                 * @StratusEventListener(
                 *     frontListener="
                 *         myInput.setAttribute('disabled', true)
                 *     "
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
});
