
# Example 5.

## Introduction.

With this example we pretends show that with the `hasClass()`, `addClass()` and `removeClass()` methods it's possible edit the CSS classes of the elements of the page DOM.

In addition, we want comments that in similay way, they exists the `hasAttribute()`, `setAttribute()` and `getAttribute()` methods for the attributes.

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

                <style>
                    .hidden {
                        display: none;
                    }
                </style>
            </head>
            <body>
                <label s-element="label">I am the label</label>
                <button s-element="button">Show/Hide</button>
            </body>
            </html>
        HTML;
    }

    public function onClickButton(): void
    {
        if ($this->label->hasClass('hidden')) {
            $this->label->removeClass('hidden');
        } else {
            $this->label->addClass('hidden');
        }
    }
}
```

## Result.

![](result.gif)