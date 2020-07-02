# TODO



## Style property

  * Create a parameter for the body property: padding-top
to be able to change the top fix bar (in [main](/main.php) and [detail](/detail.php))


## Icon

From https://realfavicongenerator.net/

```html
<link rel="manifest" href="/site.webmanifest">
<link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5">
<meta name="msapplication-TileColor" content="#da532c">
<meta name="theme-color" content="#ffffff">
```


## CSS

There is a preload going on for the CSS in [main](/main.php) and [detail](/detail.php)

```php
global $DOKU_TPL_BOOTIE_PRELOAD_CSS;

foreach ($DOKU_TPL_BOOTIE_PRELOAD_CSS as $link) {
    $htmlLink = '<link rel="stylesheet" href="' . $link['href'] . '" ';
    if ($link['crossorigin'] != "") {
        $htmlLink .= ' crossorigin="' . $link['crossorigin'] . '" ';
    }
    // No integrity here
    $htmlLink .= '>';
    ptln($htmlLink);
}
```
