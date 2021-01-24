
# Example 2.

## Introduction.

This example it's very similar to the before and it's pretends show is that on the elements that contains already the `s-element` attribute, it's possible declare events employing the attribute name `s-element-event-<event_name>`.

In this case it's declared that the click event of the button will be handled by the `clickOnTheButton` function.

## Implementation.

```php
<?php
// src/MyPage.php

use ThenLabs\StratusPHP\Plugin\SElements\AbstractPage;

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
                <button s-element="myButton" s-element-event-click="clickOnTheButton">Greet</button>
            </body>
            </html>
        HTML;
    }

    public function clickOnTheButton(): void
    {
        $this->myLabel->textContent = 'Hello ' . $this->myInput->value;
    }
}
```

## Result.

![](result.gif)