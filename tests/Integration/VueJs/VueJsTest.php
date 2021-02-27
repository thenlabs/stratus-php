<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Tests\Integration\VueJs;

use ThenLabs\StratusPHP\Plugin\SElements\AbstractPage as TestApp;
use ThenLabs\StratusPHP\Tests\SeleniumTestCase;
use ThenLabs\StratusPHP\Tests\Integration\VueJs\MyTable;

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

                            {$this->renderScripts()}
                        </body>
                        </html>
                    HTML;
                }

                public function onClickMyButton(): void
                {
                    $this->myTable->setRows([
                        ['name' => 'Andy Navarro', 'gender' => 'Male'],
                        ['name' => 'Daniel Taño', 'gender' => 'Male'],
                    ]);
                }
            };

            $myTable = new MyTable;
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
