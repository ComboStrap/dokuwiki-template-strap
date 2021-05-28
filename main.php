<?php


//Library of template function

use Combostrap\TplUtility;
use dokuwiki\Extension\Event;
use dokuwiki\Menu\PageMenu;
use dokuwiki\Menu\SiteMenu;
use dokuwiki\Menu\UserMenu;

require_once(__DIR__ . '/class/TplUtility.php');

if (!defined('DOKU_INC')) die(); /* must be run from within DokuWiki */


TplUtility::setHttpHeader();

global $ID;
global $lang;
global $ACT;
global $conf;


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
$sidebarName = $conf['sidebar'];

$hasSidebar = page_findnearest($sidebarName);
$showSideBar = $hasSidebar && ($ACT == 'show');
if ($showSideBar) {
    /**
     * Even if there is no sidebar
     * the rendering may output
     * debug information in the form of
     * an HTML comment
     */
    $sideBarHtml = TplUtility::renderBar($sidebarName);
}


/**
 * Sidekickbar
 */
$sideKickPageName = tpl_getConf(TplUtility::CONF_SIDEKICK);
$hasRightSidebar = page_findnearest($sideKickPageName);
$showSideKickBar = $hasRightSidebar && ($ACT == 'show');
if ($showSideKickBar) {
    $sideKickBarHtml = TplUtility::renderBar($sideKickPageName);
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
$gridColumns = tpl_getConf(TplUtility::CONF_GRID_COLUMNS);
$layout = p_get_metadata($ID, "layout");
if ($layout === "median") {
    $maximalWidthMain = 8;
} else {
    $maximalWidthMain = $gridColumns;
}
$sidebarScale = 3;
$sideKickBarScale = 3;
if ($showSideBar) {
    $mainGridScale = $showSideKickBar ? $gridColumns - $sidebarScale - $sideKickBarScale : $gridColumns - $sidebarScale;
} else {
    $mainGridScale = $showSideKickBar ? $gridColumns - $sideKickBarScale : $maximalWidthMain;
}

/**
 * Bootstrap meta-headers
 */
TplUtility::registerHeaderHandler();

/**
 * Default rem font size
 */
$rootStyle = "";
$htmlRem = tpl_getConf(TplUtility::CONF_REM_SIZE, null);
if ($htmlRem != null) {
    $rootStyle = "style=\"font-size:{$htmlRem}px\"";
}

/**
 * Ob checks
 * It should be null, otherwise
 * you may get a text before the HTML header
 * and it mess up the whole page
 */
$length = ob_get_length();
if ($length > 0) {
    $ob = ob_get_contents();
    ob_clean();
    // If you got this problem check that this is not a character before a  `<?php` declaration
    TplUtility::msg("A plugin has send text before the creation of the page. Because it will mess the rendering, we have deleted it. The content was: (" . $ob . ")", TplUtility::LVL_MSG_ERROR, "strap");
}

?>

<?php // DocType Required: https://getbootstrap.com/docs/5.0/getting-started/introduction/#html5-doctype ?>
<!DOCTYPE html >
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $conf['lang'] ?>" lang="<?php echo $conf['lang'] ?>" dir="<?php echo $lang['direction'] ?>" <?php echo $rootStyle ?>>
<head>

    <?php // Avoid using character entities in your HTML, provided their encoding matches that of the document (generally UTF-8) ?>
    <meta charset="utf-8">

    <?php // Responsive meta tag ?>
    <meta name="viewport" content="width=device-width,initial-scale=1"/>

    <?php // Headers ?>
    <?php tpl_metaheaders() ?>

    <title><?php TplUtility::renderPageTitle() ?></title>


    <?php // Favicon ?>
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
    global $ID;
    global $conf;
    if ($ID != $conf["start"]) {
        TplUtility::renderTrailBreadcrumb();
    }
    ?>

    <div class="row justify-content-md-center">


        <?php
        // SIDE BAR
        if ($showSideBar): ?>
            <div role="complementary" class="col-md-<?php echo($sidebarScale) ?> order-last order-md-first">

                <nav class="bs-docs-sidebar hidden-prints">


                    <?php echo $sideBarHtml ?>

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
// Footer
echo $footerBar;
// Powered By
echo TplUtility::getPoweredBy();
// The stylesheet (before indexer work and script at the end)
TplUtility::addPreloadedResources();
?>


<div class="d-none">
    <?php
    // Indexer (Background tasks)
    tpl_indexerWebBug()
    ?>
</div>


</html>
