<?php
/**
 * Template header, included in the files main.php and detail.php
 */

// must be run from within DokuWiki
if (!defined('DOKU_INC')) die();

global $conf;

?>

<nav class="fixed-top navbar-light bg-light">

    <!--    navbar-expand-lg gives:-->
    <!--       * the breakpoint at the md breakpoint-->
    <!--       * and make the content flex (no new line then)  -->
    <!--    <nav id="navBar" class="navbar navbar-expand-md">-->


    <?php
    $navBarPageName = tpl_getConf('header');
    if (page_findnearest($navBarPageName)) {
        tpl_flush();
        tpl_include_page($navBarPageName, 1, 1);

        // Breadcrumb is here to be able to have a large introduction text
        echo '<div class="container">';
        if ($conf['breadcrumbs']) {
            tpl_breadcrumbs_bootstrap();
        }
        echo '</div>';

    } else {

        $domain = 'https://combostrap.com';
        echo '<div class="container p-3" style="text-align: center">Welcome to the <a href="' . $domain . '/strap">Strap template</a>.</br>
            If you don\'t known the <a href="https://combostrap.com/strap">Strap template</a>, it\'s recommanded to read the <a href="' . $domain . '/strap">introduction</a>.</br>
            Otherwise, to create a navigation bar, create a page with the id (' . html_wikilink(':' . $navBarPageName) . ') and the <a href="' . $domain . '/navbar">navbar component</a>.
            </div>';

    }
    ?>


</nav>

