<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Tests\Integration\VueJs;

use ThenLabs\StratusPHP\Plugin\VueJs\Asset\VueJsScript;
use ThenLabs\StratusPHP\Plugin\SElements\AbstractPage as TestApp;
use ThenLabs\StratusPHP\Tests\SeleniumTestCase;

setTestCaseNamespace(__NAMESPACE__);
setTestCaseClass(SeleniumTestCase::class);

testCase('VueJsTest.php', function () {
    testCase(function () {
        setUpBeforeClassOnce(function () {
            $page = new class('', false) extends TestApp {
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
                            <div>
                                <button s-element="myButton">New Item</button>
                            </div>

                            <div>
                                {$this->renderChildren()}
                            </div>
                        </body>
                        </html>
                    HTML;
                }

                public function onClickMyButton(): void
                {
                    $myTable = $this->findChildByName('myTable');

                    $myTable->rows = [
                        ['name' => 'Andy Navarro', 'gender' => 'Male'],
                        ['name' => 'Daniel Taño', 'gender' => 'Male'],
                    ];
                }
            };

            VueJsScript::getInstance()->setUri('/node_modules/vue/dist/vue.min.js');

            $myTable = new \ThenLabs\StratusPHP\Tests\Integration\VueJs\MyTable;
            $myTable->setName('myTable');

            $page->addChild($myTable);
            $page->runPlugins();

            static::dumpApp($page, false);
            static::openApp();
        });

        test(function () {
            $button = static::findElement('button');
            $button->click();
            static::waitForResponse();

            $this->assertCount(2, static::findElements('tbody > tr'));
        });
    });
});
