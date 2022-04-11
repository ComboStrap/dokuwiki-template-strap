<?php
if (!defined('DOKU_INC')) die(); /* must be run from within DokuWiki */


// Classes
require_once(__DIR__ . '/class/TplUtility.php');
require_once(__DIR__ . '/class/Layout.php');

use ComboStrap\Layout;
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
 * Layout init
 */
$layoutObject = new Layout();// Mandatory
Event::createAndTrigger('COMBO_LAYOUT', $layoutObject);


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
echo $headerBar;

echo $layoutObject->getOrCreateArea("page-core")->toEnterHtmlTag("div");
?>



<?php // To go at the top of the page, style is for the fix top page, absolute to not participate to the grid --> ?>
<div id="dokuwiki__top" class="position-absolute"></div>


<?php
// The global message array
TplUtility::printMessage()
?>


<?php
//  A trigger to show content on the top part of the website
$data = "";// Mandatory
Event::createAndTrigger('TPL_PAGE_TOP_OUTPUT', $data);

if ($ACT === "show") {

    // sidebar
    if ($showSideBar):

        echo $layoutObject->getOrCreateArea("page-side")->toEnterHtmlTag("aside");
        echo $sideBarHtml;
        echo "</aside>";

    endif;

    echo $layoutObject->getOrCreateArea("page-main")->toEnterHtmlTag("main");

    // Readibilty: Add a p around the content to enable the reader view in Mozilla
    // https://github.com/mozilla/readability
    // But Firefox close the P because they must contain only inline element ???

    echo $outputBuffer;

    echo $mainHtml;

    /**
     * @deprecated
     */
    if ($showSideKickBar):

        echo '<aside class="slot-combo d-print-none" id="main-sidekickbar" role="complementary">';

        echo $sideKickBarHtml;

        echo '</aside>';

    endif;

    echo "</main>";

} else { // do not use the main html element for do/admin content, main is reserved for the styling of the page content ?>

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



<?php
// End page core
echo "</div>";
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
