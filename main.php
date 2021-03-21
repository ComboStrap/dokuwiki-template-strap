<?php


//Library of template function

use ComboStrap\TplConstant;
use Combostrap\TplUtility;
use dokuwiki\Extension\Event;
use dokuwiki\Menu\PageMenu;
use dokuwiki\Menu\SiteMenu;
use dokuwiki\Menu\UserMenu;

require_once(__DIR__ . '/class/TplUtility.php');
require_once(__DIR__ . '/class/TplConstant.php');

if (!defined('DOKU_INC')) die(); /* must be run from within DokuWiki */


TplUtility::setHttpHeader();

global $ID;
global $lang;
global $ACT;
global $conf;

// For the preload if any
global $DOKU_TPL_BOOTIE_PRELOAD_CSS;

/**
 * The Content first because it contains
 * also the front matter that may influence the other bars
 *
 * The content: Show, Edit, ....
 */
$mainHtml = TplUtility::tpl_content($prependTOC = false);

/**
 * Sidebar
 */
$hasSidebar = page_findnearest($conf['sidebar']);
$showSidebar = $hasSidebar && ($ACT == 'show');
if ($showSidebar) {
    $sidebarHtml = tpl_include_page($conf['sidebar'], 0, 1);
}

/**
 * Sidekickbar
 */
$hasRightSidebar = page_findnearest(tpl_getConf(TplConstant::CONF_SIDEKICK));
$showSideKickBar = $hasRightSidebar && ($ACT == 'show');
if ($showSideKickBar) {
    $sideKickBarHtml = tpl_include_page(tpl_getConf(TplConstant::CONF_SIDEKICK), 0, 1);
}

/**
 * Headerbar
 */
$headerBar = TplUtility::getHeader();

/**
 * Footerbar
 */
$footerBar = TplUtility::getFooter();


/**
 * Grid
 */
$gridColumns = tpl_getConf(TplConstant::CONF_GRID_COLUMNS);
$sidebarScale = 3;
$sideKickBarScale = 3;
if ($showSidebar) {
    $mainGridScale = $showSideKickBar ? $gridColumns - $sidebarScale - $sideKickBarScale : $gridColumns - $sidebarScale;
} else {
    $mainGridScale = $showSideKickBar ? $gridColumns - $sideKickBarScale : $gridColumns;
}

/**
 * Bootstrap meta-headers
 */
global $EVENT_HANDLER;
$method = array('\Combostrap\TplUtility', 'handleBootstrapMetaHeaders');
/**
 * A call to a method is via an array and the hook declare a string
 * @noinspection PhpParamsInspection
 */
$EVENT_HANDLER->register_hook('TPL_METAHEADER_OUTPUT', 'BEFORE', null, $method);

/**
 * Default font size
 */
$htmlRem = tpl_getConf("rem","16px");

?>

<!DOCTYPE html >
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $conf['lang'] ?>" lang="<?php echo $conf['lang'] ?>"
      dir="<?php echo $lang['direction'] ?>"
      style="font-size:<?php echo $htmlRem ?>">
<head>

    <?php tpl_metaheaders() ?>

    <!-- Be sure to have only https call -->
    <meta http-equiv="Content-Security-Policy" content="block-all-mixed-content"/>

    <title><?php TplUtility::renderPageTitle() ?></title>

    <meta name="viewport" content="width=device-width,initial-scale=1"/>

    <?php echo TplUtility::renderFaviconMetaLinks() ?>

    <?php
    /**
     * In case of a fix bar
     */
    echo TplUtility::getHeadStyleNodeForFixedTopNavbar();
    ?>

</head>
<?php
// * tpl_classes will add the dokuwiki class. See https://www.dokuwiki.org/devel:templates#dokuwiki_class
// * dokuwiki__top ID is needed for the "Back to top" utility
// * used also by some plugins
?>
<body class="dokuwiki" style="padding-top: <?php echo TplUtility::getPaddingTop() ?>px;">


<?php
echo $headerBar

// Relative positioning is important for the positioning of the pagetools
?>
<div class="container mb-3 <?php echo tpl_classes() ?> " style="position: relative">


    <?php // To go at the top of the page, style is for the fix top page --> ?>
    <div id="dokuwiki__top"></div>


    <?php
    // The global message array
    html_msgarea()
    ?>


    <?php
    //  A trigger to show content on the top part of the website
    $data = "";// Mandatory
    Event::createAndTrigger('TPL_PAGE_TOP_OUTPUT', $data);
    ?>

    <?php
    TplUtility::renderTrailBreadcrumb();
    ?>

    <div class="row">


        <?php
        // SIDE BAR
        if ($showSidebar): ?>
            <div role="complementary" class="col-md-<?php echo($sidebarScale) ?> order-last order-md-first">

                <nav class="bs-docs-sidebar hidden-prints">


                    <?php echo $sidebarHtml ?>

                </nav>

            </div>
        <?php endif; ?>


        <main class="col-md-<?php echo($mainGridScale) ?> order-first">

            <?php
            // Add a p around the content to enable the reader view in Mozilla
            // https://github.com/mozilla/readability
            // But Firefox close the P because they must contain only inline element ???
            echo $mainHtml;
            ?>

        </main>


        <?php
        // SIDE BAR
        if ($showSideKickBar): ?>

            <div role="complementary" class="col-md-<?php echo($sideKickBarScale) ?> order-xs-2 order-md-last">

                <nav class="bs-docs-sidebar hidden-prints">

                    <?php tpl_flush() ?>

                    <?php
                    echo $sideKickBarHtml

                    // <a class="back-to-top" href="#dokuwiki__top"> Back to top </a>
                    ?>

                </nav>


                <?php
                // A trigger to show content on the sidebar part of the website
                $data = "";// Mandatory
                Event::createAndTrigger('TPL_SIDEBAR_BOTTOM_OUTPUT', $data);
                ?>

            </div>
        <?php
            // end content
        endif;
        ?>

    </div>


    <?php
    // PAGE/USER/SITE ACTIONS
    if (!(tpl_getConf('privateToolbar') === 1 && empty($_SERVER['REMOTE_USER']))) { ?>
        <div id="dokuwiki__pagetools" style="z-index: 1030;" class="d-none d-md-block">
            <div class="tools">
                <ul>
                    <?php echo (new PageMenu())->getListItems(); ?>
                    <?php echo (new UserMenu())->getListItems('action'); ?>
                    <?php echo (new SiteMenu())->getListItems('action'); ?>
                    <?php // FYI: for all menu in mobile: echo (new \dokuwiki\Menu\MobileMenu())->getDropdown($lang['tools']); ?>
                </ul>
            </div>
        </div>
    <?php } ?>

</div>


<?php
echo $footerBar;
echo TplUtility::getPoweredBy();
?>



<?php
// The stylesheet (before indexer work and script at the end)
if (isset($DOKU_TPL_BOOTIE_PRELOAD_CSS)) {
    foreach ($DOKU_TPL_BOOTIE_PRELOAD_CSS as $link) {
        $htmlLink = '<link rel="stylesheet" href="' . $link['href'] . '" ';
        if ($link['crossorigin'] != "") {
            $htmlLink .= ' crossorigin="' . $link['crossorigin'] . '" ';
        }
        // No integrity here
        $htmlLink .= '>';
        ptln($htmlLink);
    }
}
?>


<div class="no">
    <?php
    // Indexer (Background tasks)
    tpl_indexerWebBug()
    ?>
</div>


</html>
