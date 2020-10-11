<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Tests\Integration;

use ThenLabs\StratusPHP\Tests\SeleniumTestCase;
use ThenLabs\StratusPHP\AbstractSugarApp as TestApp;

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
                            <input e-input type="text" name="">
                            <label e-label></label>
                            <button e-button>MyButton</button>
                        </body>
                        </html>
                    HTML;
                }

                public function onClickButton()
                {
                    $this->label->innerHTML = $this->input->value;
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
});
