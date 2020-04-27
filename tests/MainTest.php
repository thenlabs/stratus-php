<?php
declare(strict_types=1);

namespace ThenLabs\Stratus\Tests;

use ThenLabs\Stratus\AbstractApp;

setTestCaseNamespace(__NAMESPACE__);
setTestCaseClass(SeleniumTestCase::class);

testCase('MainTest.php', function () {
    test(function () {
        $app = new class extends AbstractApp {
            public function getView(array $data = []): string
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
                        <button></button>
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

        $secret = uniqid();

        static::findElement('input')->sendKeys($secret);
        static::findElement('button')->click();

        $this->assertContains(
            $secret,
            static::findElement('label')->getText()
        );
    });
});
