<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Tests\Integration\VueJs;

use ThenLabs\StratusPHP\Plugin\VueJs\AbstractComponent as AbstractVueJsComponent;
use ThenLabs\StratusPHP\Plugin\VueJs\Annotation as VueJs;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class MyTable extends AbstractVueJsComponent
{
    /**
     * @VueJs\Data
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
