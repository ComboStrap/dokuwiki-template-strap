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
 * Layout init
 */

$layoutObject = Layout::create();
Event::createAndTrigger('COMBO_LAYOUT', $layoutObject);

/**
 * Layout object was not processed (ie Combo not installed)
 */
$pageSideArea = $layoutObject->getOrCreateArea(Layout::PAGE_SIDE);
$showPageSideArea = $pageSideArea->show();
if ($showPageSideArea !== null) {
    $sideBarHtml = $pageSideArea->getHtml();
} else {
    $nearestWikiId = page_findnearest($pageSideArea->getSlotName());
    $showPageSideArea = $nearestWikiId !== false && ($ACT === 'show');
    if ($showPageSideArea) {
        $sideBarHtml = tpl_include_page($nearestWikiId, 0, 1);
    }
}

/**
 * Main Header
 */
$mainHeaderArea = $layoutObject->getOrCreateArea(Layout::MAIN_HEADER);
$showMainHeader = $mainHeaderArea->show();
if ($showMainHeader !== null) {
    $mainHeaderHtml = $mainHeaderArea->getHtml();
} else {
    $nearestMainHeader = page_findnearest($mainHeaderArea->getSlotName());
    $showMainHeader = $nearestMainHeader !== false
        && ($ACT === 'show')
        && TplUtility::isNotSlot()
        && TplUtility::isNotRootHome();
    if ($showMainHeader !== false) {
        $mainHeaderHtml = tpl_include_page($nearestMainHeader, 0, 1);
    }
}

/**
 * Main Toc
 */
$mainTocArea = $layoutObject->getOrCreateArea(Layout::MAIN_TOC);
$showMainToc = $mainTocArea->show();
if ($showMainToc !== null) {
    $mainTocHtml = $mainTocArea->getHtml();
} else {
    $nearestMainToc = page_findnearest($mainTocArea->getSlotName());
    $showMainToc = $nearestMainToc !== false
        && ($ACT === 'show')
        && TplUtility::isNotSlot()
        && TplUtility::isNotRootHome();
    if ($showMainToc !== false) {
        $mainTocHtml = tpl_include_page($nearestMainToc, 0, 1);
    }
}

/**
 * Main footer
 */
$mainFooterArea = $layoutObject->getOrCreateArea(Layout::MAIN_FOOTER);
$showMainFooter = $mainFooterArea->show();
if ($showMainFooter !== null) {
    $mainFooterHtml = $mainFooterArea->getHtml();
} else {
    $nearestMainFooter = page_findnearest($mainHeaderArea->getSlotName());
    $showMainFooter = $nearestMainFooter !== false
        && ($ACT === 'show')
        && TplUtility::isNotSlot()
        && TplUtility::isNotRootHome();
    if ($showMainFooter !== false) {
        $mainFooterHtml = tpl_include_page($nearestMainFooter, 0, 1);
    }
}


/**
 * Main Side
 */
$mainSideArea = $layoutObject->getOrCreateArea(Layout::MAIN_SIDE);
$showMainSide = $mainSideArea->show();
if ($showMainSide !== null) {
    $mainSideHtml = $mainSideArea->getHtml();
} else {
    $mainSideWikiId = page_findnearest($mainSideArea->getSlotName());
    $showMainSide = $mainSideWikiId !== false && $ACT === 'show';
    if ($showMainSide !== false) {
        $mainSideHtml = tpl_include_page($mainSideWikiId, 0, 1);
    }
}


/**
 * Page Footer / Fat Footer
 */
$pageFooterArea = $layoutObject->getOrCreateArea(Layout::PAGE_FOOTER);
$showPageFooter = $pageFooterArea->show();
if ($showPageFooter !== null) {
    $pageFooterHtml = $pageFooterArea->getHtml();
    if ($showPageFooter === true && $pageFooterHtml === null) {
        $domain = TplUtility::getApexDomainUrl();
        $pageFooterHtml = <<<EOF
<div class="container p-3" style="text-align: center">
    <p>
    Welcome to the <a href="' . $domain . '/strap">Strap template</a>. To get started, create a page with the id  {$pageFooterArea->getSlotName()} to create a footer.
    </p>
</div>
EOF;
    }
} else {
    $pageFooterWikiId = page_findnearest($pageFooterArea->getSlotName());
    $showPageFooter = $pageFooterWikiId !== false;
    if ($showPageFooter !== false) {
        $pageFooterHtml = tpl_include_page($pageFooterWikiId, 0, 1);
    }
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
$pageHeaderArea = $layoutObject->getOrCreateArea(Layout::PAGE_HEADER);
$pageHeaderHtml = "";
if ($pageHeaderArea->show() !== null) {
    $pageHeaderHtml = $pageHeaderArea->getHtml();
    if ($pageHeaderArea->show() === true && $pageHeaderHtml === null) {
        $domain = TplUtility::getApexDomainUrl();
        $pageHeaderHtml = <<<EOF
<div class="container p-3" style="text-align: center;position:relative;z-index:100">
    <p>Welcome to the <a href="$domain/">Strap template</a>.<p>
    <p>
      If you don\'t known <a href="https://combostrap.com/">ComboStrap</a>, it\'s recommended to follow the <a href="$domain/getting_started">Getting Started Guide</a>.<br/>
      Otherwise, to create a menu bar in the header, create a slot with the name (<a href="$domain/{$pageHeaderArea->getSlotName()}">{$pageHeaderArea->getSlotName()}</a>) and the <a href="$domain/menubar">menubar component</a>.
    </p>
</div>
EOF;
    }
} else {
    $pageHeaderWikiId = page_findnearest($pageHeaderArea->getSlotName());
    $showPageHeader = $pageHeaderWikiId !== false;
    if ($showPageHeader !== false) {
        $pageHeaderHtml = tpl_include_page($pageHeaderWikiId, 0, 1);
    }
}
if ($pageHeaderArea->show() === true) {
    echo $pageHeaderArea->toEnterHtmlTag();
    echo $pageHeaderHtml;
    echo $pageHeaderArea->toExitTag();
}

// The global message array
// should be just below body for absolute placement
TplUtility::printMessage();


$pageCoreLayoutArea = $layoutObject->getOrCreateArea("page-core");
if ($pageCoreLayoutArea->getAttributes() === null) {
    $pageCoreLayoutArea->setAttributes(["class" => "container position-relative"]);
}
echo $pageCoreLayoutArea->toEnterHtmlTag("div");
?>



<?php // To go at the top of the page, style is for the fix top page, absolute to not participate to the grid --> ?>
<div id="dokuwiki__top" class="position-absolute"></div>


<?php
//  A trigger to show content on the top part of the website
$data = "";// Mandatory
Event::createAndTrigger('TPL_PAGE_TOP_OUTPUT', $data);

if ($ACT === "show") {

    // sidebar / page side
    if ($showPageSideArea):

        echo $pageSideArea->toEnterHtmlTag("aside");
        echo $sideBarHtml;
        echo "</aside>";

    endif;

    echo $layoutObject->getOrCreateArea(Layout::PAGE_MAIN)->toEnterHtmlTag("main");

    echo $outputBuffer;

    if ($showMainHeader):

        echo $mainHeaderArea->toEnterHtmlTag("header");

        echo $mainHeaderHtml;

        echo '</header>';

    endif;

    if ($showMainToc):

        echo $mainTocArea->toEnterHtmlTag("nav");

        echo $mainTocHtml;

        echo '</nav>';

    endif;

    echo $layoutObject->getOrCreateArea(Layout::MAIN_CONTENT)->toEnterHtmlTag("div");

    echo $mainHtml;

    echo '</div>';

    if ($showMainSide):

        echo $mainSideArea->toEnterHtmlTag("aside");

        echo $mainSideHtml;

        echo '</aside>';

    endif;

    if ($showMainFooter):

        echo $mainFooterArea->toEnterHtmlTag("footer");

        echo $mainFooterHtml;

        echo '</footer>';

    endif;

    echo "</main>";

} else { // do not use the main html element for do/admin content, main is reserved for the styling of the page content ?>


    <?php
    // the viewport (constraint) is created by page-core
    echo $layoutObject->getOrCreateArea(Layout::PAGE_MAIN)->toEnterHtmlTag("main");
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
    echo "</main>"
    ?>

<?php } ?>

<?php echo $railBar ?>



<?php
// End page core
echo "</div>";

// Page Footer
if ($showPageFooter) {
    echo $pageFooterArea->toEnterHtmlTag("footer");
    echo $pageFooterHtml;
    // Powered By
    echo TplUtility::getPoweredBy();
    echo "</footer>";
}

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
