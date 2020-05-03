<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Tests\Unit;

use ThenLabs\StratusPHP\Element;
use ThenLabs\StratusPHP\Tests\TestCase;
use Wa72\HtmlPageDom\HtmlPageCrawler;

setTestCaseNamespace(__NAMESPACE__);
setTestCaseClass(TestCase::class);

testCase('ElementTest.php', function () {
    test(function () {
        $crawler = new HtmlPageCrawler(uniqid());

        $element = new Element(uniqid());
        $element->setCrawler($crawler);

        $cssSelector = uniqid();
        $this->assertSame(
            $element->querySelector($cssSelector),
            $element->querySelector($cssSelector)
        );
    });
});