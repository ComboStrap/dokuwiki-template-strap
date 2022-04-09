<?php
if (!defined('DOKU_INC')) die(); /* must be run from within DokuWiki */


//Library of template function
require_once(__DIR__ . '/class/TplUtility.php');

use Combostrap\TplUtility;
use dokuwiki\Extension\Event;


global $ID;
global $lang;
global $ACT;
global $conf;


/**
 * The Content first because it contains
 * also the front matter that may influence the other bars
 *
 * If the do action is edit, php plugin uses echo
 * a lot and the buffer is too small, we got then a buffer overflow
 *
 * Other action takes place further where the content should be
 */
$mainHtml = "";
if ($ACT === 'show') {

    $mainHtml = TplUtility::tpl_content($prependTOC = false);
}

/**
 * Sidebar
 */
$sidebarName = TplUtility::getSideSlotPageName();

$nearestSidebar = page_findnearest($sidebarName);
$showSideBar = $nearestSidebar !== false && ($ACT === 'show');
if ($showSideBar) {
    /**
     * Even if there is no sidebar
     * the rendering may output
     * debug information in the form of
     * an HTML comment
     */
    $sideBarHtml = TplUtility::renderSlot($nearestSidebar);
}


/**
 * Sidekickbar
 * @deprecated
 */
$sideKickPageName = TplUtility::getSideKickSlotPageName();
$hasRightSidebar = page_findnearest($sideKickPageName);
$showSideKickBar = $hasRightSidebar && ($ACT == 'show');
if ($showSideKickBar) {
    $sideKickBarHtml = TplUtility::renderSlot($sideKickPageName);
}


/**
 * Headerbar
 */
$headerBar = TplUtility::getPageHeader();

/**
 * Footerbar
 */
$footerBar = TplUtility::getFooter();


/**
 * Grid
 */
$gridColumns = tpl_getConf(TplUtility::CONF_GRID_COLUMNS);
/**
 * Layout
 *
 * See also: https://1linelayouts.glitch.me/ and https://www.cssportal.com/layout-generator/layout.php
 *
 * Two basic layouts for the web: fixed or liquid
 * A liquid design (also referred to as a fluid or dynamic design) fills the entire browser window by using percentages
 * rather than fixed pixel values to define the width / height
 *
 * dimension =
 *   "fluid" = max-width / min-height
 *   "contained" =
 *
 * In fluid web design, the widths of page elements are set proportional to the width of the screen or browser window.
 * A fluid website expands or contracts based on the width of the current viewport.
 *
 * Contained (ie fixed)
 * https://getbootstrap.com/docs/5.0/layout/containers/
 *
 */
// for the identity forms
global $ACT;
if (in_array($ACT, ["login", "resendpwd", "register", "profile"])) {
    $layout = "median";
} else {
    $layout = p_get_metadata($ID, "layout");
}
if ($layout === "median") {
    $maximalWidthMain = 8;
} else {
    $maximalWidthMain = $gridColumns;
}
$sidebarScale = 3;
$sideKickBarScale = 3;

switch ($ACT) {
    case "show":
        if ($showSideBar) {
            $mainGridScale = $showSideKickBar ? $gridColumns - $sidebarScale - $sideKickBarScale : $gridColumns - $sidebarScale;
        } else {
            $mainGridScale = $showSideKickBar ? $gridColumns - $sideKickBarScale : $maximalWidthMain;
        }
        break;
    default:
        $mainGridScale = $gridColumns;
}


/**
 * Landing page
 */
$mainIsContained = true;
if ($ACT != "show") {
    $mainIsContained = true;
} else {
    if ($layout == "landing") {
        $mainIsContained = false;
    }
}
$mainContainedClasses = "";
if ($mainIsContained) {
    $mainContainedClasses = "container mb-3";
}

/**
 * Bootstrap meta-headers
 */
TplUtility::registerHeaderHandler();

/**
 * Default rem font size
 */
$rootStyle = "";
$htmlRem = TplUtility::getRem();
if ($htmlRem != null) {
    $rootStyle = "style=\"font-size:{$htmlRem}px\"";
}

/**
 * Railbar
 * Railbar can add snippet in the head
 * And should then be could before the HTML output
 */
$railBar = TplUtility::getRailBar();

/**
 * The output buffer should be empty on show
 * and can be not empty on other do action
 */
$outputBuffer = TplUtility::outputBuffer();


?>

<?php // DocType Required: https://getbootstrap.com/docs/5.0/getting-started/introduction/#html5-doctype ?>
<!DOCTYPE html >
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $conf['lang'] ?>" lang="<?php echo $conf['lang'] ?>"
      dir="<?php echo $lang['direction'] ?>" <?php echo $rootStyle ?>>
<head>

    <?php // Avoid using character entities in your HTML, provided their encoding matches that of the document (generally UTF-8) ?>
    <meta charset="utf-8"/>

    <?php // Responsive meta tag ?>
    <meta name="viewport" content="width=device-width,initial-scale=1"/>

    <?php // Headers ?>
    <?php tpl_metaheaders() ?>

    <title><?php TplUtility::renderPageTitle() ?></title>

    <?php // Favicon ?>
    <?php echo TplUtility::renderFaviconMetaLinks() ?>



</head>
<?php
// * tpl_classes will add the dokuwiki class. See https://www.dokuwiki.org/devel:templates#dokuwiki_class
// * dokuwiki__top ID is needed for the "Back to top" utility
// * used also by some plugins
?>
<body class="dokuwiki">

<?php
echo $headerBar

// Relative positioning is important for the positioning of the pagetools
?>
<div class="<?php echo $mainContainedClasses ?> <?php echo tpl_classes() ?> " id="page-core" style="position: relative">


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

    if ($ACT === "show") {

        // sidebar
        if ($showSideBar): ?>
            <aside class="slot-combo d-print-none" id="page-side" role="complementary">
                <?php echo $sideBarHtml ?>
            </aside>
        <?php endif; ?>

        <main id="page-main">

            <?php
            // Readibilty: Add a p around the content to enable the reader view in Mozilla
            // https://github.com/mozilla/readability
            // But Firefox close the P because they must contain only inline element ???

            echo $outputBuffer;

            echo $mainHtml;

            /**
             * @deprecated
             */
            if ($showSideKickBar): ?>

                <aside class="slot-combo d-print-none" id="main-sidekickbar" role="complementary">

                    <?php echo $sideKickBarHtml; ?>

                </aside>
            <?php endif; ?>

        </main>


    <?php } else { // do not use the main html element for do/admin content, main is reserved for the styling of the page content ?>
        <main id="page-main">
            <?php
            // all other action are using the php buffer
            // we can then have an overflow
            // the buffer is flushed
            // this is why we output the content of do/admin page here
            echo TplUtility::tpl_content($prependTOC = false);
            ?>
        </main>
    <?php } ?>

    <?php echo $railBar ?>

</div>


<?php
// Footer
echo $footerBar;
// The stylesheet (before indexer work and script at the end)
TplUtility::addPreloadedResources();
?>


<div class="d-none">
    <?php
    // Indexer (Background tasks)
    tpl_indexerWebBug()
    ?>
</div>

</body>
</html>
