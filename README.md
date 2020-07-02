# Strap Web Template

## About

`Strap` is the template of [ComboStrap](https://combostrap.com/strap)


## Features


  * [bootstrap integrated](http://getbootstrap.com/)
  * [Top Fixed Navbar](http://getbootstrap.com/components/#navbar-fixed-top)
  * `Footer` page called `footerbar`. Same functionality than for the [sidebar](https://www.dokuwiki.org/faq:sidebar#i_have_a_sidebar_functionality_how_can_i_create_or_edit_a_sidebar_now) but with a page called `footer`.
  * `header` page called `headerbar`. Same functionality than for the [sidebar](https://www.dokuwiki.org/faq:sidebar#i_have_a_sidebar_functionality_how_can_i_create_or_edit_a_sidebar_now) but with a page called `footer`.
  * `sidekick` page called `sidekickbar`. Same functionality than for the [sidebar](https://www.dokuwiki.org/faq:sidebar#i_have_a_sidebar_functionality_how_can_i_create_or_edit_a_sidebar_now) but with a page called `footer`.
  * Performance: All Javascript and CSS stylesheet are served asynchronously (defer for js and at the end for css)
  * Resources (Javascript or Stylesheet) served from CDN or locally
  * Load the third library via CDN or locally

Options:

  * `Menu in sidebar` via [Panel](http://getbootstrap.com/components/#panels-heading) with the [mini-map plugin](https://gerardnico.com/dokuwiki/minimap). It will add a list of page of the current namespace.
  * `Headers numbering` and [Toc](https://www.dokuwiki.org/toc) below the first header with the [Rplus rendering](https://www.dokuwiki.org/plugin:rplus) plugin

## Version

  * Support php version 5.6, 7.0, 7.1, 7.2, 7.3
  * Bootstrap 4. For the exact version, see the [js directory](./js/)

## Configuration

The configuration can be change with the [Configuration Manager Plugin](https://www.dokuwiki.org/plugin:config)

  * `footer` : the footer name page to search. Default to `footerbar`
  * `header` : the header name page to search. Default to `headerbar`
  * `sidekick` : the name page if the right side bar. Default to `sidekickbar`
  * `cdn` : if this configuration switch is on, you'll get the Javascript whenever possible from a CDN



## Support

  * The [styling plugin](https://www.dokuwiki.org/plugin:styling) will complain that it doesn't found some Javascript. You can disable it

## Dev

For developers

The function `tpl_strap_meta_header` found in the file [tpl_lib_strap](./tpl_lib_strap.php)

  * control the headers and is call via the registration of the event `TPL_METAHEADER_OUTPUT`
  * control the Jquery version. Not logged in, Bootstrap, logged in Dokuwiki (with ui,..)

Ter info, the template file are:
  * [main.php](./main.php): The main page. [Doc](https://www.dokuwiki.org/devel:templates:main.php)
  * [detail.php](./detail.php): The template to show the detail of an image. [Doc](https://www.dokuwiki.org/devel:templates:detail.php)
  * [mediamanager.php](./mediamanager.php): The template to show the media manager.

## Release

### Current

  * Forked from the [Bootie template](https://github.com/gerardnico/dokuwiki-template-bootie)
  * A [test](./_test/php.test.php) was introduced to check the php version.
  * Difference with the Bootie template
     * a new sidebar `sidekickbar` was introduced.
     * the grid is 3 columns with at:
        * the left: the sidebar,
        * the center: the page
        * the right: the sidekick bar
     * the grid is a custom bootstrap grid of 1280 large and 16 columns which means that the bootstrap CSS is served locally
     * the sidebar is now on the left side
     * the footer is now a bootstrap container


## Note

  * The site tool and the page tool are in the same menu called Tools.

  * There is two entry points:

     * the file [main.php](main.php) - that shows the page
     * the file [detail.php](detail.php) - that shows the image

## Todo ?

  * [JqueryUI Bootstrap](https://cdn.rawgit.com/arschmitz/jqueryui-bootstrap-adapter/v0.3.0/index.html)
  * [tagline](https://www.dokuwiki.org/config:tagline) is used in the [manifest](https://www.dokuwiki.org/devel:manifest) description

## Dev Note

  * To beat the CSS cache, just open the file `local.php` and modify it
