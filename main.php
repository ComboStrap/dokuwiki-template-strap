<?php


//Library of template function
require_once('tpl_lib_strap.php');

if (!defined('DOKU_INC')) die(); /* must be run from within DokuWiki */
header('X-UA-Compatible: IE=edge,chrome=1');

global $ID;
global $lang;
global $ACT;
global $conf;

$hasSidebar = page_findnearest($conf['sidebar']);
$showSidebar = $hasSidebar && ($ACT == 'show');

$hasRightSidebar = page_findnearest(tpl_getConf('sidekickbar'));
$showRightSidebar = $hasRightSidebar && ($ACT == 'show');

global $EVENT_HANDLER;
$EVENT_HANDLER->register_hook('TPL_METAHEADER_OUTPUT', 'BEFORE', null, 'tpl_strap_meta_header');

$title = p_get_metadata($ID, "title");
if ($title !=""){

    $pageTitle = $title;

} else {

    // Home page
    if ($ID == "start") {
        $pageTitle = $conf['title'];
        if ($conf['tagline']) {
            $pageTitle .= ' - ' . $conf['tagline'];
        }
    } else {
        $pageTitle = tpl_pagetitle($ID, true) . strip_tags($conf['title']);
    }

}
?>

<!DOCTYPE html >

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $conf['lang'] ?>" lang="<?php echo $conf['lang'] ?>"
      dir="<?php echo $lang['direction'] ?>"
      style="font-size:<?php echo tpl_getConf("rem") ?>">
<head>

    <?php tpl_metaheaders() ?>

    <meta charset="utf-8"/>

    <!-- Be sure to have only htts call -->
    <meta http-equiv="Content-Security-Policy" content="block-all-mixed-content"/>

    <title><?php echo strip_tags($pageTitle) ?></title>

    <meta name="viewport" content="width=device-width,initial-scale=1"/>

    <?php echo tpl_strap_favicon() ?>


</head>
<body role="document" style="padding-top: 6rem;">


<?php
// The header (used also in detail.php)
include('tpl_header.php')
?>
<!--
  * tpl_classes will add the dokuwiki class. See https://www.dokuwiki.org/devel:templates#dokuwiki_class
  * dokuwiki__top ID is needed for the "Back to top" utility
  * used also by some plugins
-->
<!-- Relative positioning is important for the positioning of the pagetools -->
<div class="container <?php echo tpl_classes() ?>" id="dokuwiki__top" style="position: relative">

    <!-- TAGLINE (TODO put in on the head) -->
    <!--    --><?php //if ($conf['tagline']): ?>
    <!--        <p class="claim">--><?php //echo $conf['tagline']; ?><!--</p>-->
    <!--    --><?php //endif ?>

    <!-- The global message array -->
    <?php html_msgarea() ?>

    <!-- A trigger to show content on the top part of the website -->
    <?php
    $data = "";// Mandatory
    trigger_event('TPL_PAGE_TOP_OUTPUT', $data);
    ?>

    <div class="row mt-3">

        <!-- SIDE BAR -->
        <?php if ($showSidebar): ?>
            <nav role="complementary" class="col-md-3 order-last order-md-first">
                <!-- Below data-spy="affix" data-offset-top="230"-->
                <nav class="bs-docs-sidebar hidden-prints">

                    <?php tpl_flush() ?>

                    <?php tpl_include_page($conf['sidebar'], 1, 1) ?>

                </nav>

            </nav>
        <?php endif; ?>


        <main role="main"
              class="col-md-<?php echo(($showSidebar) ? ($showRightSidebar ? 10 : 13) : ($showRightSidebar ? 13 : 16)) ?> order-first pl-md-4 pr-md-4">


            <!-- You are here -->
            <?php
            if ($conf['youarehere']) {
                tpl_youarehere_bootstrap();
            }
            ?>


            <!-- The content: Show, Edit, .... -->
            <?php tpl_flush() ?>


            <!-- Add a p around the content to enable the reader view in Mozilla -->
            <!-- https://github.com/mozilla/readability -->
            <!-- But Firefox close the P because they must contain only inline element ???-->
            <?php tpl_content($prependTOC = false) ?>

            <?php //tpl_pageinfo() ?>
            <?php tpl_flush() ?>
        </main>


        <!-- SIDE BAR -->
        <?php if ($showRightSidebar): ?>

            <nav role="complementary" class="col-md-3 order-xs-2 order-md-last">

                <!-- Below data-spy="affix" data-offset-top="230"-->
                <nav class="bs-docs-sidebar hidden-prints">

                    <?php tpl_flush() ?>

                    <?php tpl_include_page(tpl_getConf('sidekickbar'), 1, 1) ?>

                    <!--                    <a class="back-to-top" href="#dokuwiki__top"> Back to top </a>-->

                </nav>

                <!-- A trigger to show content on the sidebar part of the website -->
                <?php
                $data = "";// Mandatory
                trigger_event('TPL_SIDEBAR_BOTTOM_OUTPUT', $data);
                ?>

            </nav>
        <?php endif; ?>
        <!-- /content -->
    </div>


    <!-- PAGE/USER/SITE ACTIONS -->
    <?php if (!empty($_SERVER['REMOTE_USER'])) { ?>
        <div id="dokuwiki__pagetools" style="z-index: 1030;" class="d-none d-md-block">
            <div class="tools">
                <ul>
                    <?php echo (new \dokuwiki\Menu\PageMenu())->getListItems(); ?>
                    <?php echo (new \dokuwiki\Menu\UserMenu())->getListItems('action'); ?>
                    <?php echo (new \dokuwiki\Menu\SiteMenu())->getListItems('action'); ?>
                    <?php // FYI: for all menu in mobile: echo (new \dokuwiki\Menu\MobileMenu())->getDropdown($lang['tools']); ?>
                </ul>
            </div>
        </div>
    <?php } ?>

</div>
<!-- /wrapper -->

<!-- Footer (used also in details.php -->
<?php include('tpl_footer.php') ?>


<!-- The stylesheet (before indexer work and script at the end) -->
<?php
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
?>

<!-- Indexer -->
<div class="no"><?php tpl_indexerWebBug() /* provide DokuWiki housekeeping, required in all templates */ ?></div>


</html>
