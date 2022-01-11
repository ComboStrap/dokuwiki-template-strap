# Strap Web Template

> Volatile WebSite Generator based on Easy Markup File, Bootstrap Styled, and the power of a Wiki Platform

![ComboStrap - Easy Markup WebSite Generator](https://raw.githubusercontent.com/ComboStrap/combo/main/resources/images/banner-combostrap.png "combostrap website bootstrap dokuwiki")

[![Build Status](https://app.travis-ci.com/ComboStrap/dokuwiki-template-strap.svg?branch=main)](https://app.travis-ci.com/ComboStrap/dokuwiki-template-strap)

## About

`Strap` is the companion template of [ComboStrap](https://combostrap.com/)


## Features


  * [bootstrap 5 integrated](https://combostrap.com/bootstrap)
  * [Menubar (Top Fixed or not)](http://combostrap.com/menubar)
  * Dynamic [Layout](http://combostrap.com/layout):
    * `holy grail`
    * `median`
    * `landing`
  * Dynamic template with [slots](http://combostrap.com/slot):
    * `Footer` slot
    * `header` slot
    * `Side` slot
    * `sidekick` slot
  * [Rail bar](http://combostrap.com/railbar) for the actions (site and page tools)
  * [Fast as hell](http://combostrap.com/performance):
    * One Stylesheet dependency available globally from CDN
    * All Javascript are served asynchronously
    * Lazy image loading
  * and all [ComboStrap component](http://combostrap.com/component)


## Release

See the [dedicated page](https://combostrap.com/release-a-log-of-all-combostrap-changes-and-release-9g2si7zb)


## Dev

For developers

The function `tpl_strap_meta_header` found in the file [tpl_lib_strap](class/TplUtility.php)

* control the headers and is call via the registration of the event `TPL_METAHEADER_OUTPUT`
* control the Jquery version. Not logged in, Bootstrap, logged in Dokuwiki (with ui,..)

Ter info, the template file are:
* [main.php](./main.php): The main page. [Doc](https://www.dokuwiki.org/devel:templates:main.php)
* [detail.php](./detail.php): The template to show the detail of an image. [Doc](https://www.dokuwiki.org/devel:templates:detail.php)
* [mediamanager.php](./mediamanager.php): The template to show the media manager.
