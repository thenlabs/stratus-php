
# Example 11.

## Introduction.

With this example we pretends show that with the `alert()`, `confirm()` and `prompt()` functions it's possible show and get information from the browser using the equivalent native functions.

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
                <label s-element="myLabel"></label>
                <button s-element="alertButton">Show Alert</button>
                <button s-element="confirmButton">Show Confirm</button>
                <button s-element="promptButton">Show Prompt</button>
            </body>
            </html>
        HTML;
    }

    public function onClickAlertButton(): void
    {
        $this->browser->alert('My Alert');
    }

    public function onClickConfirmButton(): void
    {
        $this->myLabel->textContent = $this->browser->confirm('Do you confirm this?') ?
            'Accepted' : 'Cancelled'
        ;
    }

    public function onClickPromptButton(): void
    {
        $this->myLabel->textContent = 'Hello ' . $this->browser->prompt('What is your name?');
    }
}
```

## Result.

![](result.gif)