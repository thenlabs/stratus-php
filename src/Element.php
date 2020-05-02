<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP;

use ThenLabs\StratusPHP\JavaScript\JavaScriptInstanceInterface;
use ThenLabs\Components\CompositeComponentInterface;
use ThenLabs\Components\CompositeComponentTrait;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class Element implements CompositeComponentInterface, JavaScriptInstanceInterface
{
    use CompositeComponentTrait;

    protected $cssSelector;
    protected $attributes = [];

    public function __construct(string $cssSelector)
    {
        $this->cssSelector = $cssSelector;
    }

    public static function getJavaScriptClassMembers(): string
    {
        return <<<JAVASCRIPT
        JAVASCRIPT;
    }

    public function getJavaScriptCreateInstanceScript(): string
    {
        $jsAttributes = '';

        foreach ($this->attributes as $attribute => $value) {
            if ($value === null) {
                continue;
            }

            $jsAttribute = var_export($attribute, true);
            $jsValue = var_export($value, true);

            $jsAttributes .= <<<JAVASCRIPT
                element.setAttribute({$jsAttribute}, {$jsValue});\n
            JAVASCRIPT;
        }

        return <<<JAVASCRIPT
            var element = document.querySelector('{$this->cssSelector}');
            {$jsAttributes}
        JAVASCRIPT;
    }

    public function click(): void
    {
    }

    public function setAttribute(string $attribute, $value): void
    {
        $this->attributes[$attribute] = $value;
    }

    public function getAttribute(string $attribute)
    {
        return $this->attributes[$attribute] ?? null;
    }
}
