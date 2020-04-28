<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP;

use ThenLabs\ComposedViews\AbstractCompositeView;
use ThenLabs\StratusPHP\Asset\StratusScript;
use ThenLabs\StratusPHP\Asset\StratusInitScript;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 * @abstract
 */
abstract class AbstractApp extends AbstractCompositeView
{
    protected $controllerUri;

    public function __construct(string $controllerUri)
    {
        parent::__construct();

        $this->controllerUri = $controllerUri;

        $this->addFilter(function ($event) {
            $stratusScript = new StratusScript('stratus-js', null, '');

            $stratusInitScript = new StratusInitScript('stratus-init-script', null, '');
            $stratusInitScript->setApp($this);

            $event->filter('body')->append($stratusScript->render());
            $event->filter('body')->append($stratusInitScript->render());
        });
    }

    public function getToken(): string
    {
        return uniqid('token', true);
    }

    public function getControllerUri(): string
    {
        return $this->controllerUri;
    }

    public function filter(string $cssSelector): Element
    {
        $element = new Element($cssSelector);

        return $element;
    }
}
