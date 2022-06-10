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
 * Railbar can add snippet in the head
 * And should then be before the head output
 */
$railBar = TplUtility::getRailBar();

$poweredBy = TplUtility::getPoweredBy();

/**
 *
 * If the do action is edit, php plugin uses echo
 * a lot and the buffer is too small, we got then a buffer overflow
 *
 * Other action takes place further where the content should be
 */

$htmlPageShow = "";
if ($ACT === 'show') {

    /**
     * The Content first because it contains
     * also the front matter that may influence the other slot
     */
    $mainHtml = TplUtility::tpl_content($prependTOC = false);

    /**
     * Page (Header/Footer/Side)
     */
    $pageSideHtml = TplUtility::getXhtmlForSlotName(TplUtility::getSideSlotPageName());
    $pageFooterHtml = TplUtility::getXhtmlForSlotName(TplUtility::getFooterSlotPageName());
    $pageHeaderHtml = TplUtility::getXhtmlForSlotName(TplUtility::getHeaderSlotPageName());

    /**
     * Main (Header/Footer/Side)
     */
    $mainHeaderHtml = "";
    $mainFooterHtml = "";
    if (TplUtility::isNotSlot() && TplUtility::isNotRootHome()) {
        $mainHeaderHtml = TplUtility::getXhtmlForSlotName(TplUtility::SLOT_MAIN_HEADER);
        $mainFooterHtml = TplUtility::getXhtmlForSlotName(TplUtility::SLOT_MAIN_FOOTER);
    }
    $mainSideHtml = TplUtility::getXhtmlForSlotName(TplUtility::getMainSideSlotName());;

    /**
     * The output buffer should be empty on show
     */
    $outputBuffer = TplUtility::outputBuffer();

    $toc = tpl_toc(true);
    $htmlPageShow = <<<EOF
<header>$pageHeaderHtml</header>
<!-- To go at the top of the page, style is for the fix top page, absolute to not participate in a grid -->
<div id=\"dokuwiki__top\" class=\"position-absolute\"></div>
<div id="page-core" class="container position-relative d-flex justify-content-md-center">
    $outputBuffer
    <aside id="main-side" class="col-md-3 order-last order-md-first">$pageSideHtml</aside>
    <main id="page-main" class="col-md-9 order-first">
        <header id=\"main-header\">$mainHeaderHtml</header>
        <nav id=\"main-toc\">$toc</nav>
        <div id=\"main-content\">$mainHtml</div>
        <aside id=\"main-side\">$mainSideHtml</aside>
        <header id=\"main-footer\">$mainFooterHtml</header>
    </main>
    $railBar
</div>
<footer>{$pageFooterHtml}{$poweredBy}</footer>
EOF;

} else {

    /**
     * Header/Footer/Buffer processing
     */
    $pageHeaderHtml = TplUtility::getXhtmlForSlotName(TplUtility::getHeaderSlotPageName());
    $pageFooterHtml = TplUtility::getXhtmlForSlotName(TplUtility::getFooterSlotPageName());
    $outputBuffer = TplUtility::outputBuffer(); // The output buffer can be not empty on other do action

}

/**
 * Layout System
 */
try {
    TplUtility::checkSameStrapAndComboVersion();
    $filename = "../../plugins/combo/vendor/autoloady.php";
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
    msg($e->getMessage(), -1, '', '', MSG_MANAGERS_ONLY);
}


/**
 * Bootstrap meta-headers function registration
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


// The global message array
// should be just below body for absolute placement
TplUtility::printMessage();


if ($ACT === "show") {

    echo $htmlPageShow;

} else {


    /**
     * Output
     */
    echo "<header>$pageHeaderHtml</header>";
    echo "<div id=\"page-core\" class=\"container position-relative d-flex justify-content-md-center\">";
    echo $outputBuffer;

    // All other action others than show
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
    echo $railBar;
    echo "</div>";
    echo "<footer>$pageFooterHtml</footer>";

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
