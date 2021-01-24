
# Example 7.

## Introduction.

With this example we pretends show that with the `frontListener` of the `ThenLabs\StratusPHP\Annotation\EventListener` annotation it is possible to specify an event listener in the browser, and even cancel the execution of the server listener.

It is also important to note that from the JavaScript code it is possible to refer to the elements marked with the `s-element` attribute. In addition to this, you can also access the data of the events that will be taken to the server and even modify them, and finally, we wanted to show that it is possible to cancel the execution of the server listener when needed.

In the example, only the browser listener it is executed when it is introduced on the textbox a lower letter.

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

    /**
     * @EventListener(
     *     fetchData={"key", "keyCode"},
     *     frontListener="
     *         if (! (eventData.keyCode >= 97 && eventData.keyCode <= 122)) {
     *             myLabel.textContent = 'Only lower letters they are accepted.';
     *             event.backListener = false;
     *         }
     *     "
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