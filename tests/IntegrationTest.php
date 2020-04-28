<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Tests;

use ThenLabs\StratusPHP\AbstractApp;

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
                        <!DOCTYPE html>
                        <html lang="en">
                        <head>
                            <meta charset="UTF-8">
                            <title>Document</title>
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
});
