<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Tests\Integration;

use ThenLabs\StratusPHP\Plugin\SElements\AbstractPage as TestApp;
use ThenLabs\StratusPHP\Plugin\VueJs\Annotation as VueJs;
use ThenLabs\StratusPHP\Plugin\VueJs\AbstractComponent as AbstractVueJsComponent;
use ThenLabs\StratusPHP\Tests\SeleniumTestCase;

setTestCaseNamespace(__NAMESPACE__);
setTestCaseClass(SeleniumTestCase::class);

testCase('VueJsTest.php', function () {
    testCase(function () {
        setUpBeforeClassOnce(function () {
            $table = new class extends AbstractVueJsComponent
            {
                /**
                 * @VueJs\Data(type="array")
                 */
                protected $rows = [
                    ['name' => 'Andy', 'gender' => 'Male'],
                ];

                public function getView(): string
                {
                    return <<<HTML
                        <table>
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Name</th>
                                    <th>Gender</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="row in rows">
                                    <td></td>
                                    <td>{{ row.name }}</td>
                                    <td>{{ row.gender }}</td>
                                </tr>
                            </tbody>
                        </table>
                    HTML;
                }
            };

            $page = new class('') extends TestApp
            {
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
                    $this->setRows([
                        ['name' => 'Andy Navarro', 'gender' => 'Male'],
                        ['name' => 'Daniel Taño', 'gender' => 'Male'],
                    ]);
                }
            };

            $page->addChild($table);

            static::dumpApp($page);
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
