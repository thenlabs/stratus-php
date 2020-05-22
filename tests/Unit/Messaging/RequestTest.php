<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Tests\Unit\Messaging;

use ThenLabs\StratusPHP\Messaging\Request;
use ThenLabs\StratusPHP\Tests\TestCase;

setTestCaseNamespace(__NAMESPACE__);
setTestCaseClass(TestCase::class);

testCase('RequestTest.php', function () {
    test(function () {
        $data = [
            'token' => uniqid(),
            'componentData' => range(1, mt_rand(1, 10)),
            'operations' => range(1, mt_rand(1, 10)),
            'eventName' => uniqid(),
        ];

        $request = Request::createFromJson(json_encode($data));

        $this->assertEquals($data['token'], $request->getToken());
        $this->assertEquals($data['operations'], $request->getOperations());
        $this->assertEquals($data['componentData'], $request->getComponentData());
        $this->assertEquals($data['eventName'], $request->getEventName());
    });
});
