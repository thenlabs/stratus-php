<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Tests\Unit\Plugin\PageDom;

use ThenLabs\StratusPHP\AbstractPage;
use ThenLabs\StratusPHP\Plugin\PageDom\Element;
use ThenLabs\StratusPHP\Exception\InvokationBeforeBootException;
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

    testCase(function () {
        setUp(function () {
            $this->expectException(InvokationBeforeBootException::class);
            $this->element = new Element(uniqid());
            $this->element->setPage($this->createMock(AbstractPage::class));
        });

        test(function () {
            $this->element->setAttribute(uniqid(), uniqid());
        });

        test(function () {
            $this->element->getAttribute(uniqid());
        });

        test(function () {
            $this->element->hasAttribute(uniqid());
        });

        test(function () {
            $this->element->removeAttribute(uniqid());
        });

        test(function () {
            $this->element->hasClass(uniqid());
        });

        test(function () {
            $this->element->addClass(uniqid());
        });

        test(function () {
            $this->element->removeClass(uniqid());
        });

        test(function () {
            $this->element->setStyle(uniqid(), uniqid());
        });

        test(function () {
            $this->element->getStyle(uniqid());
        });
    });
});
