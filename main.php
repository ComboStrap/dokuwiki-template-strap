<?php

// Classes
require_once(__DIR__ . '/class/TplUtility.php');

use ComboStrap\FetcherPage;
use ComboStrap\LogUtility;
use ComboStrap\PluginUtility;
use Combostrap\TplUtility;


global $ID;
global $lang;
global $ACT;
global $conf;

/**
 * ################################
 * Content generation
 * ################################
 * Generate the content, start the process first
 * to collect the buffer if any
 * then create the whole HTML page
 */

/**
 * Railbar can add snippet in the head
 * And should then be before the head output
 */
$railBar = TplUtility::getRailBar();

/**
 * Powered By
 */
$poweredBy = TplUtility::getPoweredBy();

/**
 * HTML body content generation
 */
if ($ACT === 'show') {

    /**
     * Layout System
     */
    $htmlPageShow = "";
    $basicLayoutMessageInCaseOfError = "The page layout module could not be used, defaulting to the basic layout that is not optimized.";
    try {

        /**
         * Combo and Strap installed, same version
         * Otherwise throw
         */
        TplUtility::checkSameStrapAndComboVersion();

        /**
         * Loading all combo classes
         */
        $filename = DOKU_PLUGIN . "combo/vendor/autoload.php";
        if (!file_exists($filename)) {
            throw new \RuntimeException("Internal Error: Combo was not found. Combo is installed ?");
        }
        require_once($filename);
        /**
         * Checking that the page fetcher entry point exists
         * From their, combo and strap have the same version, all
         * error are internal errors
         */
        $fetcherClass = "\ComboStrap\FetcherPage";
        if (!class_exists($fetcherClass)) {
            throw new \RuntimeException("Internal Error: Page Fetcher component was not found.");
        }
        if (!method_exists($fetcherClass, "createPageFetcherFromRequestedPage")) {
            throw new \RuntimeException("Internal Error: Page Fetcher entry point was not found.");
        }
        if (!method_exists($fetcherClass, "getFetchPathAsHtmlString")) {
            throw new \RuntimeException("Internal Error: Page Fetcher get point was not found.");
        }
        $htmlPageShow = FetcherPage::createPageFetcherFromRequestedPage()->getFetchPathAsHtmlString();

    } catch (Exception $e) {
        // not the same version or not installed
        $message = "{$e->getMessage()} $basicLayoutMessageInCaseOfError";
        if (TplUtility::isTest()) {
            throw new RuntimeException($message, 0, $e);
        }
        msg($message, -1, '', '', MSG_MANAGERS_ONLY);
    }

    if ($htmlPageShow === "") {

        /**
         * No valid combo Installed, default template
         */

        /**
         * The Content first because it contains
         * also the front matter that may influence the other slot
         */
        try {
            $mainHtml = TplUtility::tpl_content($prependTOC = false);
        } catch (Exception $e) {
            $mainHtml = $e->getMessage();
        }

        /**
         * Page (Header/Footer/Side)
         */
        try {
            $pageSideHtml = TplUtility::getXhtmlForSlotName(TplUtility::getSideSlotPageName());
        } catch (Exception $e) {
            $pageSideHtml = $e->getMessage();
        }
        try {
            $pageFooterHtml = TplUtility::getXhtmlForSlotName(TplUtility::getFooterSlotPageName());
        } catch (Exception $e) {
            $pageFooterHtml = $e->getMessage();
        }
        try {
            $pageHeaderHtml = TplUtility::getXhtmlForSlotName(TplUtility::getHeaderSlotPageName());
        } catch (Exception $e) {
            $pageHeaderHtml = $e->getMessage();
        }

        /**
         * Main (Header/Footer/Side)
         */
        $mainHeaderHtml = "";
        $mainFooterHtml = "";
        if (TplUtility::isNotSlot() && TplUtility::isNotRootHome()) {
            try {
                $mainHeaderHtml = TplUtility::getXhtmlForSlotName(TplUtility::SLOT_MAIN_HEADER);
            } catch (Exception $e) {
                $mainHeaderHtml = $e->getMessage();
            }
            try {
                $mainFooterHtml = TplUtility::getXhtmlForSlotName(TplUtility::SLOT_MAIN_FOOTER);
            } catch (Exception $e) {
                $mainFooterHtml = $e->getMessage();
            }
        }
        $mainSideHtml = TplUtility::getXhtmlForSlotName(TplUtility::getMainSideSlotName());;

        /**
         * The output buffer should be empty on show
         */
        $outputBuffer = TplUtility::outputBuffer();
        /**
         * Space between side and main
         */
        if (empty($pageSideHtml)) {
            $sideWidth = 0;
            $mainWidth = 12;
        } else {
            $sideWidth = 3;
            $mainWidth = 8;
        }

        $toc = tpl_toc(true);
        $htmlPageShow = <<<EOF
<header>$pageHeaderHtml</header>
<!-- To go at the top of the page, style is for the fix top page, absolute to not participate in a grid -->
<div id="dokuwiki__top" class="position-absolute"></div>
<div id="page-core" class="container position-relative d-flex justify-content-md-center">
    $outputBuffer
    <aside id="main-side" class="col-md-$sideWidth order-last order-md-first">$pageSideHtml</aside>
    <main id="page-main" class="col-md-$mainWidth order-first">
        <header id="main-header\">$mainHeaderHtml</header>
        <nav id="main-toc">$toc</nav>
        <div id="main-content">$mainHtml</div>
        <aside id="main-side">$mainSideHtml</aside>
        <header id="main-footer">$mainFooterHtml</header>
    </main>
    $railBar
</div>
<footer>{$pageFooterHtml}{$poweredBy}</footer>
EOF;
    }

} else {

    /**
     * Header/Footer/Buffer processing
     * for other action than show
     * (ie edit, admin, ...)
     */
    $pageHeaderHtml = TplUtility::getXhtmlForSlotName(TplUtility::getHeaderSlotPageName());
    $pageFooterHtml = TplUtility::getXhtmlForSlotName(TplUtility::getFooterSlotPageName());
    $outputBuffer = TplUtility::outputBuffer(); // The output buffer can be not empty on other do action via plugin

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
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $conf['lang'] ?>" lang="<?php echo $conf['lang'] ?>"
      dir="<?php echo $lang['direction'] ?>" <?php echo $rootStyle ?>>
<head>

    <?php // Avoid using character entities in your HTML, provided their encoding matches that of the document (generally UTF-8) ?>
    <meta charset="utf-8"/>

    <?php // Responsive meta tag ?>
    <meta name="viewport" content="width=device-width,initial-scale=1"/>

    <?php // Headers ?>
    <?php tpl_metaheaders() ?>

    <title><?php try {
            echo TplUtility::getPageTitle();
        } catch (Exception $e) {
            msg($e->getMessage(), -1, '', '', MSG_MANAGERS_ONLY);
        }
        ?></title>

    <?php // Favicon ?>
    <?php echo TplUtility::renderFaviconMetaLinks() ?>


</head>
<?php
/**
 * {@link tpl_classes} will add the dokuwiki class. See https://www.dokuwiki.org/devel:templates#dokuwiki_class
 * dokuwiki__top ID is needed for the "Back to top" utility
 * used also by some plugins
 */
$tplClasses = tpl_classes();
?>
<body class="<?php echo $tplClasses; ?> position-relative">

<?php

// The global message array
// should be just below body for absolute placement
print TplUtility::printMessage();


if ($ACT === "show") {

    echo $htmlPageShow;

} else {

    /**
     * If the do action is other than show (such as edit, ...)
     * php plugin uses echo a lot and the buffer is too small, we got then a buffer overflow
     */
    global $ACT;
    switch ($ACT) {
        case "preview": // edit preview
        case "edit": // edit
        case "media": // media manager
            $mainClass = "col-12 mb-3"; // hamburger
            break;
        default:
        case "login": // login
        case "resendpwd": // passwd resend
        case "register": // register form
        case "profile": // profile form
        case "search": // search
        case "recent": // the revisions for the website
        case "index": // the website index
        case "diff": // diff between revisions
        case "revisions": // Known as old revisions (old version of the page)
        case "admin": // admin page
            $mainClass = "col-md-8 mx-auto"; // median
            break;
    }

    /**
     * Output
     */
    echo "<header>$pageHeaderHtml</header>";
    echo "<div id=\"page-core\" class=\"container position-relative justify-content-md-center mt-3\">";
    echo $outputBuffer;

    // All other action others than show
    // the viewport (constraint) is created by page-core
    echo "<main class=\"$mainClass\">";
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
    // Indexer (Background tasks), mandatory
    tpl_indexerWebBug()
    ?>
</div>

</body>
</html>
