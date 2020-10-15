<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\JavaScript;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class JavaScriptUtils implements JavaScriptClassInterface
{
    public static function getJavaScriptClassMembers(): string
    {
        return <<<JAVASCRIPT
            static alert(text) {
                alert(text);
            }

            static redirect(url) {
                window.location.href = url;
            }
        JAVASCRIPT;
    }
}
