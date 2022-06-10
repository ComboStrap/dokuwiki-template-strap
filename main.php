<?php

// Classes
require_once(__DIR__ . '/class/TplUtility.php');

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
 * Layout System
 */
try {
    TplUtility::checkSameStrapAndComboVersion();
    $filename = "../../plugins/combo/vendor/autoloadyolo.php";
    if (file_exists($filename)) {
        require_once($filename);
        if (class_exists("\ComboStrap\Layout")) {
            $layoutObject = Layout::create();
            Event::createAndTrigger('COMBO_LAYOUT', $layoutObject);
            return;
        }
    } else {
        msg("The autoloader of the combo plugin has not been found. You should upgrade combo.", -1, '', '', MSG_USERS_ONLY);
    }
} catch (Exception $e) {
    // not the same version or not installed
    msg($e->getMessage(),-1,'','', MSG_MANAGERS_ONLY);
}



/**
 * Layout object was not processed (ie Combo not installed)
 */
$nearestWikiId = page_findnearest(TplUtility::getSideSlotPageName());
$showPageSideArea = $nearestWikiId !== false && ($ACT === 'show');
$sideBarHtml = "";
if ($showPageSideArea) {
    $sideBarHtml = tpl_include_page($nearestWikiId, 0, 1);
}


/**
 * Main Header
 */
$nearestMainHeader = page_findnearest(TplUtility::SLOT_MAIN_HEADER);
$showMainHeader = $nearestMainHeader !== false
    && ($ACT === 'show')
    && TplUtility::isNotSlot()
    && TplUtility::isNotRootHome();
$mainHeaderHtml = "";
if ($showMainHeader !== false) {
    $mainHeaderHtml = tpl_include_page($nearestMainHeader, 0, 1);
}


/**
 * Main footer
 */
$nearestMainFooter = page_findnearest(TplUtility::SLOT_MAIN_FOOTER);
$showMainFooter = $nearestMainFooter !== false
    && ($ACT === 'show')
    && TplUtility::isNotSlot()
    && TplUtility::isNotRootHome();
$mainFooterHtml = "";
if ($showMainFooter !== false) {
    $mainFooterHtml = tpl_include_page($nearestMainFooter, 0, 1);
}


/**
 * Main Side
 */
$mainSideWikiId = page_findnearest(TplUtility::getMainSideSlotName());
$showMainSide = $mainSideWikiId !== false && $ACT === 'show';
$mainSideHtml = "";
if ($showMainSide !== false) {
    $mainSideHtml = tpl_include_page($mainSideWikiId, 0, 1);
}


/**
 * Page Footer / Fat Footer
 */
$pageFooterWikiId = page_findnearest(TplUtility::getFooterSlotPageName());
$showPageFooter = $pageFooterWikiId !== false;
$pageFooterHtml = "";
if ($showPageFooter !== false) {
    $pageFooterHtml = tpl_include_page($pageFooterWikiId, 0, 1);
}


/**
 * Grid
 */
$gridColumns = tpl_getConf(TplUtility::CONF_GRID_COLUMNS);


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
/**
 * * tpl_classes will add the dokuwiki class. See https://www.dokuwiki.org/devel:templates#dokuwiki_class
 * dokuwiki__top ID is needed for the "Back to top" utility
 * used also by some plugins
 */
?>
<body class="dokuwiki position-relative">

<?php
/**
 * Page Header
 */

$pageHeaderWikiId = page_findnearest(TplUtility::getHeaderSlotPageName());
$showPageHeader = $pageHeaderWikiId !== false;
$pageHeaderHtml = "";
if ($showPageHeader !== false) {
    $pageHeaderHtml = tpl_include_page($pageHeaderWikiId, 0, 1);
}
echo "<header>$pageHeaderHtml</header>";


// The global message array
// should be just below body for absolute placement
TplUtility::printMessage();

echo "<div id=\"page-core\" class=\"container position-relative d-flex justify-content-md-center\">";


// To go at the top of the page, style is for the fix top page, absolute to not participate to the grid -->
echo "<div id=\"dokuwiki__top\" class=\"position-absolute\"></div>";

//  A trigger to show content on the top part of the website
$data = "";// Mandatory
Event::createAndTrigger('TPL_PAGE_TOP_OUTPUT', $data);

if ($ACT === "show") {

    $toc = tpl_toc(true);
    echo <<<EOF
<aside id="main-side" class="col-md-3 order-last order-md-first">$sideBarHtml</aside>
<main id="page-main" class="col-md-9 order-first">

$outputBuffer
    <header id=\"main-header\">$mainHeaderHtml</header>
    <nav id=\"main-toc\">$toc</nav>
    <div id=\"main-content\">$mainHtml</div>
    <aside id=\"main-side\">$mainSideHtml</aside>
    <header id=\"main-footer\">$mainFooterHtml</header>

</main>
EOF;

} else {

    // do not use the main html element for do/admin content, main is reserved for the styling of the page content

    // the viewport (constraint) is created by page-core
    echo "<main>";
    /**
     * all other action are using the php buffer
     * we can then have an overflow
     * the buffer is flushed
     * this is why we output the content of do/admin page here
     * the standard dokuwiki function {@link tpl_content()} is used because
     * a admin page may create a toc See $plugin->getTOC() in {@link tpl_toc()}
     */
    tpl_flush();
    tpl_content();
    tpl_flush();
    echo "</main>";

}

echo $railBar;


// End page core
echo "</div>";

// Page Footer
echo "<footer>" . $pageFooterHtml . TplUtility::getPoweredBy() . "</footer>";

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
