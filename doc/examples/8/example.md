
# Example 8.

## Introduction.

This example is very similar to the previous one and is only intended to show that it is also possible to specify the JavaScript code of the front event handler in a function of the class.

This way may be more appropriate when the script is of considerable size since as can be seen, when using the HEREDOC syntax (<<<JAVASCRIPT...JAVASCRIPT), many IDEs and code editors will display syntax highlighting.

## Implementation.

```php
<?php
// src/MyPage.php

use ThenLabs\StratusPHP\Plugin\SElements\AbstractPage;
use ThenLabs\StratusPHP\Annotation\EventListener;

class MyPage extends AbstractPage
{
    public function getView(): string
    {
        return <<<HTML
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Document</title>
            </head>
            <body>
                <input s-element="myInput" type="text">
                <label s-element="myLabel"></label>
            </body>
            </html>
        HTML;
    }

    public function myFrontListener(): string
    {
        return <<<JAVASCRIPT
            if (! (eventData.keyCode >= 97 && eventData.keyCode <= 122)) {
                myLabel.textContent = 'Only lower letters they are accepted.';
                event.backListener = false;
            }
        JAVASCRIPT;
    }

    /**
     * @EventListener(
     *     fetchData={"key", "keyCode"},
     *     frontListener="myFrontListener"
     * )
     */
    public function onKeypressMyInput($event): void
    {
        $eventData = $event->getEventData();

        $this->myLabel->textContent = "key: {$eventData['key']}, keyCode: {$eventData['keyCode']}";
    }
}
```

## Result.

![](result.gif)