
# Example 1.

## Introduction.

With this example we pretends show that the HTML elements that have the `s-element` attribute may will be manipulated in real time from the PHP class of the application.

In addition to this, we want to highlight that the functions of the class whose name is of the type `on <event_name> <component_name>` will automatically be assigned as handlers of the respective component event. In the example you can see that the `onClickMyButton` function will handle the` click` event of the `myButton` component.

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
                <button s-element="myButton">Greet</button>
            </body>
            </html>
        HTML;
    }

    public function onClickMyButton(): void
    {
        $this->myLabel->textContent = 'Hello ' . $this->myInput->value;
    }
}
```

## Result.

![](result.gif)