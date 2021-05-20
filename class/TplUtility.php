<?php
/**
 * Copyright (c) 2020. ComboStrap, Inc. and its affiliates. All Rights Reserved.
 *
 * This source code is licensed under the GPL license found in the
 * COPYING  file in the root directory of this source tree.
 *
 * @license  GPL 3 (https://www.gnu.org/licenses/gpl-3.0.en.html)
 * @author   ComboStrap <support@combostrap.com>
 *
 */

namespace ComboStrap;

use Doku_Event;
use dokuwiki\Cache\CacheRenderer;
use dokuwiki\Extension\Event;

require(__DIR__."/BarCache.php");

use Exception;

/**
 * Class TplUtility
 * @package ComboStrap
 * Utility class
 */
class TplUtility
{

    /**
     * Constant for the function {@link msg()}
     * -1 = error, 0 = info, 1 = success, 2 = notify
     */
    const LVL_MSG_ERROR = -1;
    const LVL_MSG_INFO = 0;
    const LVL_MSG_SUCCESS = 1;
    const LVL_MSG_WARNING = 2;
    const LVL_MSG_DEBUG = 3;
    const TEMPLATE_NAME = 'strap';
    const CONF_HEADER = "headerbar";
    /**
     * The bar are also used to not add a {@link \action_plugin_combo_metacanonical}
     *
     */
    const CONF_FOOTER = "footerbar";
    const CONF_HEIGHT_FIXED_TOP_NAVBAR = 'heightFixedTopNavbar';

    /**
     * @deprecated for  {@link TplUtility::CONF_BOOTSTRAP_VERSION_STYLESHEET}
     */
    const CONF_BOOTSTRAP_VERSION = "bootstrapVersion";
    /**
     * @deprecated for  {@link TplUtility::CONF_BOOTSTRAP_VERSION_STYLESHEET}
     */
    const CONF_BOOTSTRAP_STYLESHEET = "bootstrapStylesheet";

    /**
     * Stylesheet and Boostrap should have the same version
     * This conf is a mix between the version and the stylesheet
     *
     * majorVersion.0.0 - stylesheetname
     */
    const CONF_BOOTSTRAP_VERSION_STYLESHEET = "bootstrapVersionStylesheet";
    /**
     * The separator in {@link TplUtility::CONF_BOOTSTRAP_VERSION_STYLESHEET}
     */
    const BOOTSTRAP_VERSION_STYLESHEET_SEPARATOR = " - ";
    const DEFAULT_BOOTSTRAP_VERSION_STYLESHEET = "5.0.1" . self::BOOTSTRAP_VERSION_STYLESHEET_SEPARATOR . "bootstrap";

    /**
     * Jquery UI
     */
    const CONF_JQUERY_DOKU = 'jQueryDoku';
    const CONF_REM_SIZE = "remSize";
    const CONF_GRID_COLUMNS = "gridColumns";
    const CONF_USE_CDN = "useCDN";
    const CONF_SIDEKICK = "sidekickbar";
    const CONF_PRELOAD_CSS = "preloadCss"; // preload all css ?


    /**
     * @var array|null
     */
    private static $TEMPLATE_INFO = null;


    /**
     * Print the breadcrumbs trace with Bootstrap class
     *
     * @param string $sep Separator between entries
     * @return bool
     * @author Nicolas GERARD
     *
     *
     */
    static function renderTrailBreadcrumb($sep = '�')
    {

        global $conf;
        global $lang;

        //check if enabled
        if (!$conf['breadcrumbs']) return false;

        $crumbs = breadcrumbs(); //setup crumb trace

        echo '<nav id="breadcrumb" aria-label="breadcrumb" class="my-3">' . PHP_EOL;

        $i = 0;
        // Try to get the template custom breadcrumb
        // $breadCrumb = tpl_getLang('breadcrumb');
        // if ($breadCrumb == '') {
        //    // If not present for the language, get the default one
        //    $breadCrumb = $lang['breadcrumb'];
        // }

        // echo '<span id="breadCrumbTitle" ">' . $breadCrumb . ':   </span>' . PHP_EOL;
        echo '<ol class="breadcrumb py-1 px-2" style="background-color:unset">' . PHP_EOL;
        print '<li class="pr-2" style="display:flex;font-weight: 200">' . $lang['breadcrumb'] . '</li>';

        foreach ($crumbs as $id => $name) {
            $i++;

            if ($i == 0) {
                print '<li class="breadcrumb-item active">';
            } else {
                print '<li class="breadcrumb-item">';
            }
            if ($name == "start") {
                $name = "Home";
            }
            tpl_link(wl($id), hsc($name), 'title="' . $name . '" style="width: 100%;z-index:10"');

            print '</li>' . PHP_EOL;

        }
        echo '</ol>' . PHP_EOL;
        echo '</nav>' . PHP_EOL;
        return true;
    }

    /**
     *
     * Return the padding top in pixel that must be applied when using a top bar
     * @return int
     */
    public static function getPaddingTop()
    {
        // The padding top for the top fix bar
        $paddingTop = 0;
        $heightTopBar = tpl_getConf(self::CONF_HEIGHT_FIXED_TOP_NAVBAR, 0);
        if ($heightTopBar != 0) {
            $paddingTop = $heightTopBar + 10;
        }
        return $paddingTop;
    }


    /**
     * When using a topbar, all header and top element should
     * get this style in order to navigate correctly
     * @return string
     * As the top-nav-bar is fix, the two below values must be equal to the navbar offset
     * in order to get a correct navigation to the anchor
     * See http://stackoverflow.com/questions/17181355/boostrap-using-fixed-navbar-and-anchor-tags-to-jump-to-sections
     */
    public static function getHeadStyleNodeForFixedTopNavbar()
    {
        $headStyle = "";
        $heightTopBar = tpl_getConf(self::CONF_HEIGHT_FIXED_TOP_NAVBAR);
        if ($heightTopBar !== 0) {
            $paddingTop = 2 * $heightTopBar + 10; // + 10 to get the message area not below the topnavbar
            $marginTop = -2 * $heightTopBar;
            $topHeaderStyle = "padding-top:{$paddingTop}px;margin-top:{$marginTop}px;z-index:-1";

            $headStyle = <<<EOF
<style>
    main > h1, main > h2, main > h3, main > h4, main h5, #dokuwiki__top {
    $topHeaderStyle
    }
</style>
EOF;
        }
        return $headStyle;
    }

    /**
     * @param string $text add a comment into the HTML page
     */
    private static function addAsHtmlComment($text)
    {
        print_r('<!-- TplUtility Comment: ' . hsc($text) . '-->');
    }

    private static function getApexDomainUrl()
    {
        return self::getTemplateInfo()["url"];
    }

    private static function getTemplateInfo()
    {
        if (self::$TEMPLATE_INFO == null) {
            self::$TEMPLATE_INFO = confToHash(__DIR__ . '/../template.info.txt');
        }
        return self::$TEMPLATE_INFO;
    }

    private static function getVersion()
    {
        return "v" . self::getTemplateInfo()['version'] . " (" . self::getTemplateInfo()['date'] . ")";
    }

    private static function getStrapUrl()
    {
        return self::getTemplateInfo()["strap"];
    }

    /**
     * ???
     */
    public static function setHttpHeader()
    {
        header('X-UA-Compatible: IE=edge');
    }

    public static function registerHeaderHandler()
    {
        global $EVENT_HANDLER;
        $method = array('\Combostrap\TplUtility', 'handleBootstrapMetaHeaders');
        /**
         * A call to a method is via an array and the hook declare a string
         * @noinspection PhpParamsInspection
         */
        $EVENT_HANDLER->register_hook('TPL_METAHEADER_OUTPUT', 'BEFORE', null, $method);
    }

    /**
     * Add the preloaded CSS resources
     * at the end
     */
    public static function addPreloadedResources()
    {
        // For the preload if any
        global $preloadedCss;
        //
        // Note: Adding this css in an animationFrame
        // such as https://github.com/jakearchibald/svgomg/blob/master/src/index.html#L183
        // would be difficult to test
        if (isset($preloadedCss)) {
            foreach ($preloadedCss as $link) {
                $htmlLink = '<link rel="stylesheet" href="' . $link['href'] . '" ';
                if ($link['crossorigin'] != "") {
                    $htmlLink .= ' crossorigin="' . $link['crossorigin'] . '" ';
                }
                if (!empty($link['class'])) {
                    $htmlLink .= ' class="' . $link['class'] . '" ';
                }
                // No integrity here
                $htmlLink .= '>';
                ptln($htmlLink);
            }
        }
    }

    /**
     * @param $linkData - an array of link style sheet data
     * @return array - the array with the preload attributes
     */
    private static function toPreloadCss($linkData)
    {
        /**
         * Save the stylesheet to load it at the end
         */
        global $preloadedCss;
        $preloadedCss[] = $linkData;

        /**
         * Modify the actual tag data
         * Change the loading mechanism to preload
         */
        $linkData['rel'] = 'preload';
        $linkData['as'] = 'style';
        return $linkData;
    }

    public static function getBootStrapVersion()
    {
        $bootstrapStyleSheetVersion = tpl_getConf(TplUtility::CONF_BOOTSTRAP_VERSION_STYLESHEET, TplUtility::DEFAULT_BOOTSTRAP_VERSION_STYLESHEET);
        $bootstrapStyleSheetArray = explode(self::BOOTSTRAP_VERSION_STYLESHEET_SEPARATOR, $bootstrapStyleSheetVersion);
        return $bootstrapStyleSheetArray[0];
    }

    public static function getStyleSheetConf()
    {
        $bootstrapStyleSheetVersion = tpl_getConf(TplUtility::CONF_BOOTSTRAP_VERSION_STYLESHEET, TplUtility::DEFAULT_BOOTSTRAP_VERSION_STYLESHEET);
        $bootstrapStyleSheetArray = explode(self::BOOTSTRAP_VERSION_STYLESHEET_SEPARATOR, $bootstrapStyleSheetVersion);
        return $bootstrapStyleSheetArray[1];
    }

    /**
     * Return the XHMTL for the bar or null if not found
     *
     * An adaptation from {@link tpl_include_page()}
     * to make the cache namespace
     *
     * @param $barName
     * @return string|null
     *
     */
    public static function renderBar($barName)
    {

        /**
         * Find the first file with the same name
         * in the tree
         */
        $useAcl = true;
        $rev = '';
        $physicalBarId = page_findnearest($barName, $useAcl);
        if ($physicalBarId === false) {
            return null;
        }
        $physicalBarFile = wikiFN($physicalBarId, $rev);


        /**
         * Id of the bar
         */
        global $ID;
        $actualNamespace = getNS($ID);
        $logicalBarId = $barName;
        resolve_pageid($actualNamespace, $logicalBarId, $exists);
        /**
         * When running a bar rendering
         * The global ID should become the id of bar
         * (needed for parsing)
         * The $ID is restored at the end of the function
         */
        $keep = $ID;
        $ID = $logicalBarId;


        /**
         * The code below is adapted from {@link p_cached_output()}
         * $ret = p_cached_output($file, 'xhtml', $pageid);
         *
         * We don't use {@link CacheRenderer}
         * because the cache key is the physical file
         */
        global $conf;
        $format = 'xhtml';

        $cache = new BarCache($logicalBarId, $physicalBarFile, $format);
        if ($cache->useCache()) {
            $parsed = $cache->retrieveCache(false);
            if ($conf['allowdebug'] && $format == 'xhtml') {
                $parsed .= "\n<!-- bar cachefile {$cache->cache} used -->\n";
            }
        } else {
            /**
             * Adapted from {@link p_cached_instructions()}
             */
            $instructions = p_cached_instructions($physicalBarFile, false, $logicalBarId);
            $parsed = p_render($format, $instructions, $info);
            if ($info['cache'] && $cache->storeCache($parsed)) {
                if ($conf['allowdebug'] && $format == 'xhtml') {
                    $parsed .= "\n<!-- no bar cachefile used, but created {$cache->cache} -->\n";
                }
            } else {
                $cache->removeCache();                     //try to delete cachefile
                if ($conf['allowdebug'] && $format == 'xhtml') {
                    $parsed .= "\n<!-- no bar cachefile used, caching forbidden -->\n";
                }
            }
        }

        // restore ID
        $ID = $keep;

        return $parsed;

    }


    /**
     * Hierarchical breadcrumbs
     *
     * This will return the Hierarchical breadcrumbs.
     *
     * Config:
     *    - $conf['youarehere'] must be true
     *    - add $lang['youarehere'] if $printPrefix is true
     *
     * @param bool $printPrefix print or not the $lang['youarehere']
     * @return string
     */
    function renderHierarchicalBreadcrumb($printPrefix = false)
    {

        global $conf;
        global $lang;

        // check if enabled
        if (!$conf['youarehere']) return;

        // print intermediate namespace links
        $htmlOutput = '<ol class="breadcrumb">' . PHP_EOL;

        // Print the home page
        $htmlOutput .= '<li>' . PHP_EOL;
        if ($printPrefix) {
            $htmlOutput .= $lang['youarehere'] . ' ';
        }
        $page = $conf['start'];
        $htmlOutput .= tpl_link(wl($page), '<span class="glyphicon glyphicon-home" aria-hidden="true"></span>', 'title="' . tpl_pagetitle($page, true) . '"', $return = true);
        $htmlOutput .= '</li>' . PHP_EOL;

        // Print the parts if there is more than one
        global $ID;
        $idParts = explode(':', $ID);
        if (count($idParts) > 1) {

            // Print the parts without the last one ($count -1)
            $page = "";
            for ($i = 0; $i < count($idParts) - 1; $i++) {

                $page .= $idParts[$i] . ':';

                // Skip home page of the namespace
                // if ($page == $conf['start']) continue;

                // The last part is the active one
//            if ($i == $count) {
//                $htmlOutput .= '<li class="active">';
//            } else {
//                $htmlOutput .= '<li>';
//            }

                $htmlOutput .= '<li>';
                // html_wikilink because the page has the form pagename: and not pagename:pagename
                $htmlOutput .= html_wikilink($page);
                $htmlOutput .= '</li>' . PHP_EOL;

            }
        }

        // Skipping Wiki Global Root Home Page
//    resolve_pageid('', $page, $exists);
//    if(isset($page) && $page == $idPart.$idParts[$i]) {
//        echo '</ol>'.PHP_EOL;
//        return true;
//    }
//    // skipping for namespace index
//    $page = $idPart.$idParts[$i];
//    if($page == $conf['start']) {
//        echo '</ol>'.PHP_EOL;
//        return true;
//    }

        // print current page
//    print '<li>';
//    tpl_link(wl($page), tpl_pagetitle($page,true), 'title="' . $page . '"');
        $htmlOutput .= '</li>' . PHP_EOL;
        // close the breadcrumb
        $htmlOutput .= '</ol>' . PHP_EOL;
        return $htmlOutput;

    }


    /*
     * Function return the page name from an id
     * @author Nicolas GERARD
     *
     * @param string $sep Separator between entries
     * @return bool
     */

    function getPageTitle($id)
    {

        // page names
        $name = noNSorNS($id);
        if (useHeading('navigation')) {
            // get page title
            $title = p_get_first_heading($id, METADATA_RENDER_USING_SIMPLE_CACHE);
            if ($title) {
                $name = $title;
            }
        }
        return $name;

    }

    function renderSearchForm($ajax = true, $autocomplete = true)
    {
        global $lang;
        global $ACT;
        global $QUERY;

        // don't print the search form if search action has been disabled
        if (!actionOK('search')) return false;

        print '<form id="navBarSearch" action="' . wl() . '" accept-charset="utf-8" class="search form-inline my-lg-0" id="dw__search" method="get" role="search">';
        print '<input type="hidden" name="do" value="search" />';
        print '<label class="sr-only" for="search">Search Term</label>';
        print '<input type="text" ';
        if ($ACT == 'search') print 'value="' . htmlspecialchars($QUERY) . '" ';
        print 'placeholder="' . $lang['btn_search'] . '..." ';
        if (!$autocomplete) print 'autocomplete="off" ';
        print 'id="qsearch__in" accesskey="f" name="id" class="edit form-control" title="[F]" />';
//    print '<button type="submit" title="'.$lang['btn_search'].'">'.$lang['btn_search'].'</button>';
        if ($ajax) print '<div id="qsearch__out" class="ajax_qsearch JSpopup"></div>';
        print '</form>';
        return true;
    }

    /**
     * This is a fork of tpl_actionlink where I have added the class parameters
     *
     * Like the action buttons but links
     *
     * @param string $type action command
     * @param string $pre prefix of link
     * @param string $suf suffix of link
     * @param string $inner innerHML of link
     * @param bool $return if true it returns html, otherwise prints
     * @param string $class the class to be added
     * @return bool|string html or false if no data, true if printed
     * @see    tpl_get_action
     *
     * @author Adrian Lang <mail@adrianlang.de>
     */
    function renderActionLink($type, $class = '', $pre = '', $suf = '', $inner = '', $return = false)
    {
        global $lang;
        $data = tpl_get_action($type);
        if ($data === false) {
            return false;
        } elseif (!is_array($data)) {
            $out = sprintf($data, 'link');
        } else {
            /**
             * @var string $accesskey
             * @var string $id
             * @var string $method
             * @var bool $nofollow
             * @var array $params
             * @var string $replacement
             */
            extract($data);
            if (strpos($id, '#') === 0) {
                $linktarget = $id;
            } else {
                $linktarget = wl($id, $params);
            }
            $caption = $lang['btn_' . $type];
            if (strpos($caption, '%s')) {
                $caption = sprintf($caption, $replacement);
            }
            $akey = $addTitle = '';
            if ($accesskey) {
                $akey = 'accesskey="' . $accesskey . '" ';
                $addTitle = ' [' . strtoupper($accesskey) . ']';
            }
            $rel = $nofollow ? 'rel="nofollow" ' : '';
            $out = $pre . tpl_link(
                    $linktarget, (($inner) ? $inner : $caption),
                    'class="nav-link action ' . $type . ' ' . $class . '" ' .
                    $akey . $rel .
                    'title="' . hsc($caption) . $addTitle . '"', true
                ) . $suf;
        }
        if ($return) return $out;
        echo $out;
        return true;
    }


    /**
     * @return array
     * Return the headers needed by this template
     *
     * @throws Exception
     */
    static function getBootstrapMetaHeaders()
    {

        // The version
        $bootstrapVersion = TplUtility::getBootStrapVersion();
        if ($bootstrapVersion === false) {
            /**
             * Strap may be called for test
             * by combo
             * In this case, the conf may not be reloaded
             */
            self::reloadConf();
            $bootstrapVersion = TplUtility::getBootStrapVersion();
            if ($bootstrapVersion === false) {
                throw new Exception("Bootstrap version should not be false");
            }
        }
        $scriptsMeta = self::buildBootstrapMetas($bootstrapVersion);

        // if cdn
        $useCdn = tpl_getConf(self::CONF_USE_CDN);


        // Build the returned Js script array
        $jsScripts = array();
        foreach ($scriptsMeta as $key => $script) {
            $path_parts = pathinfo($script["file"]);
            $extension = $path_parts['extension'];
            if ($extension === "js") {
                $src = DOKU_BASE . "lib/tpl/strap/bootstrap/$bootstrapVersion/" . $script["file"];
                if ($useCdn) {
                    if (isset($script["url"])) {
                        $src = $script["url"];
                    }
                }
                $jsScripts[$key] =
                    array(
                        'src' => $src,
                        'defer' => null
                    );
                if (isset($script['integrity'])) {
                    $jsScripts[$key]['integrity'] = $script['integrity'];
                    $jsScripts[$key]['crossorigin'] = 'anonymous';
                }
            }
        }

        $css = array();
        $cssScript = $scriptsMeta['css'];
        $href = DOKU_BASE . "lib/tpl/strap/bootstrap/$bootstrapVersion/" . $cssScript["file"];
        if ($useCdn) {
            if (isset($script["url"])) {
                $href = $script["url"];
            }
        }
        $css['css'] =
            array(
                'href' => $href,
                'rel' => "stylesheet"
            );
        if (isset($script['integrity'])) {
            $css['css']['integrity'] = $script['integrity'];
            $css['css']['crossorigin'] = 'anonymous';
        }


        return array(
            'script' => $jsScripts,
            'link' => $css
        );


    }

    /**
     * @return array - A list of all available stylesheets
     * This function is used to build the configuration as a list of files
     */
    static function getStylesheetsForMetadataConfiguration()
    {
        $cssVersionsMetas = self::getStyleSheetsFromJsonFile();
        $listVersionStylesheetMeta = array();
        foreach ($cssVersionsMetas as $bootstrapVersion => $cssVersionMeta) {
            foreach ($cssVersionMeta as $fileName => $values) {
                $listVersionStylesheetMeta[] = $bootstrapVersion . TplUtility::BOOTSTRAP_VERSION_STYLESHEET_SEPARATOR . $fileName;
            }
        }
        return $listVersionStylesheetMeta;
    }

    /**
     *
     * @param $version - return only the selected version if set
     * @return array - an array of the meta JSON custom files
     */
    static function getStyleSheetsFromJsonFile($version = null)
    {

        $jsonAsArray = true;
        $stylesheetsFile = __DIR__ . '/../bootstrap/bootstrapStylesheet.json';
        $styleSheets = json_decode(file_get_contents($stylesheetsFile), $jsonAsArray);
        if ($styleSheets == null) {
            self::msg("Unable to read the file {$stylesheetsFile} as json");
        }
        $localStyleSheetsFile = __DIR__ . '/../bootstrap/bootstrapLocal.json';
        if (file_exists($localStyleSheetsFile)) {
            $localStyleSheets = json_decode(file_get_contents($localStyleSheetsFile), $jsonAsArray);
            if ($localStyleSheets == null) {
                self::msg("Unable to read the file {$localStyleSheets} as json");
            }
            foreach ($styleSheets as $bootstrapVersion => &$stylesheetsFiles) {
                if (isset($localStyleSheets[$bootstrapVersion])) {
                    $stylesheetsFiles = array_merge($stylesheetsFiles, $localStyleSheets[$bootstrapVersion]);
                }
            }

        }

        if (isset($version)) {
            if (!isset($styleSheets[$version])) {
                self::msg("The bootstrap version ($version) could not be found in the custom CSS file ($stylesheetsFile, or $localStyleSheetsFile)");
            } else {
                $styleSheets = $styleSheets[$version];
            }
        }
        return $styleSheets;
    }

    /**
     *
     * Build from all Bootstrap JSON meta files only one array
     * @param $version
     * @return array
     *
     */
    static function buildBootstrapMetas($version)
    {

        $jsonAsArray = true;
        $bootstrapJsonFile = __DIR__ . '/../bootstrap/bootstrapJavascript.json';
        $bootstrapMetas = json_decode(file_get_contents($bootstrapJsonFile), $jsonAsArray);
        // Decodage problem
        if ($bootstrapMetas == null) {
            self::msg("Unable to read the file {$bootstrapJsonFile} as json");
            return array();
        }
        if (!isset($bootstrapMetas[$version])) {
            self::msg("The bootstrap version ($version) could not be found in the file $bootstrapJsonFile");
            return array();
        }
        $bootstrapMetas = $bootstrapMetas[$version];


        // Css
        $bootstrapCssFile = TplUtility::getStyleSheetConf();
        $bootstrapCustomMetas = self::getStyleSheetsFromJsonFile($version);

        if (!isset($bootstrapCustomMetas[$bootstrapCssFile])) {
            self::msg("The bootstrap custom file ($bootstrapCssFile) could not be found in the custom CSS files for the version ($version)");
        } else {
            $bootstrapMetas['css'] = $bootstrapCustomMetas[$bootstrapCssFile];
        }


        return $bootstrapMetas;
    }

    /**
     * @param Doku_Event $event
     * @param $param
     * Function that handle the META HEADER event
     *   * It will add the Bootstrap Js and CSS
     *   * Make all script and resources defer
     * @throws Exception
     */
    static function handleBootstrapMetaHeaders(Doku_Event &$event, $param)
    {

        $debug = tpl_getConf('debug');
        if ($debug) {
            self::addAsHtmlComment('Request: ' . json_encode($_REQUEST));
        }


        $newHeaderTypes = array();
        $bootstrapHeaders = self::getBootstrapMetaHeaders();
        $eventHeaderTypes = $event->data;
        foreach ($eventHeaderTypes as $headerType => $headerData) {
            switch ($headerType) {

                case "link":
                    // index, rss, manifest, search, alternate, stylesheet
                    // delete edit
                    $bootstrapCss = $bootstrapHeaders[$headerType]['css'];
                    $headerData[] = $bootstrapCss;

                    // preload all CSS is an heresy as it creates a FOUC (Flash of non-styled element)
                    // but we known it now and this is
                    $cssPreloadConf = tpl_getConf(self::CONF_PRELOAD_CSS);
                    $newLinkData = array();
                    foreach ($headerData as $linkData) {
                        switch ($linkData['rel']) {
                            case 'edit':
                                break;
                            case 'stylesheet':
                                /**
                                 * Preloading default to the configuration
                                 */
                                $preload = $cssPreloadConf;
                                /**
                                 * Preload can be set at the array level with the critical attribute
                                 * If the preload attribute is present
                                 * We get that for instance for css animation style sheet
                                 * that are not needed for rendering
                                 */
                                if (isset($linkData["critical"])) {
                                    $critical = $linkData["critical"];
                                    $preload = !(filter_var($critical, FILTER_VALIDATE_BOOLEAN));
                                    unset($linkData["critical"]);
                                }
                                if ($preload) {
                                    $newLinkData[] = TplUtility::toPreloadCss($linkData);
                                } else {
                                    $newLinkData[] = $linkData;
                                }
                                break;
                            default:
                                $newLinkData[] = $linkData;
                                break;
                        }
                    }

                    $newHeaderTypes[$headerType] = $newLinkData;
                    break;

                case "script":

                    $newScriptData = array();
                    // A variable to hold the Jquery scripts
                    // jquery-migrate, jquery, jquery-ui ou jquery.php
                    // see https://www.dokuwiki.org/config:jquerycdn
                    $jqueryDokuScripts = array();
                    foreach ($headerData as $scriptData) {

                        $critical = false;
                        if (isset($scriptData["critical"])) {
                            $critical = $scriptData["critical"];
                            unset($scriptData["critical"]);
                        }

                        // defer is only for external resource
                        // if this is not, this is illegal
                        if (isset($scriptData["src"])) {
                            if (!$critical) {
                                $scriptData['defer'] = "true";
                            }
                        }

                        if (isset($scriptData["type"])) {
                            $type = strtolower($scriptData["type"]);
                            if ($type == "text/javascript") {
                                unset($scriptData["type"]);
                            }
                        }

                        // The charset attribute on the script element is obsolete.
                        if (isset($scriptData["charset"])) {
                            unset($scriptData["charset"]);
                        }

                        // Jquery ?
                        $jqueryFound = false;
                        // script may also be just an online script without the src attribute
                        if (array_key_exists('src', $scriptData)) {
                            $jqueryFound = strpos($scriptData['src'], 'jquery');
                        }
                        if ($jqueryFound === false) {
                            $newScriptData[] = $scriptData;
                        } else {
                            $jqueryDokuScripts[] = $scriptData;
                        }

                    }

                    // Add Jquery at the beginning
                    if (empty($_SERVER['REMOTE_USER']) && tpl_getConf(self::CONF_JQUERY_DOKU) == 0) {
                        // We take the Jquery of Bootstrap
                        $newScriptData = array_merge($bootstrapHeaders[$headerType], $newScriptData);
                    } else {
                        // Logged in
                        // We take the Jqueries of doku and we add Bootstrap
                        $newScriptData = array_merge($jqueryDokuScripts, $newScriptData); // js
                        $newScriptData[] = $bootstrapHeaders[$headerType]['popper'];
                        $newScriptData[] = $bootstrapHeaders[$headerType]['js'];
                    }


                    $newHeaderTypes[$headerType] = $newScriptData;
                    break;
                default:
                case "meta":
                case "style":
                    // generator, color, robots, keywords
                    // nothing to do pick them all
                    $newHeaderTypes[$headerType] = $headerData;
                    break;

            }
        }

        if ($debug) {
            self::addAsHtmlComment('Script Header : ' . json_encode($newHeaderTypes['script']));
        }
        $event->data = $newHeaderTypes;


    }

    /**
     * Returns the icon link as created by https://realfavicongenerator.net/
     *
     *
     *
     * @return string
     */
    static function renderFaviconMetaLinks()
    {

        $return = '';

        // FavIcon.ico
        $possibleLocation = array(':wiki:favicon.ico', ':favicon.ico', 'images/favicon.ico');
        $return .= '<link rel="shortcut icon" href="' . tpl_getMediaFile($possibleLocation, $fallback = true) . '" />' . NL;

        // Icon Png
        $possibleLocation = array(':wiki:favicon-32x32.png', ':favicon-32x32.png', 'images/favicon-32x32.png');
        $return .= '<link rel="icon" type="image/png" sizes="32x32" href="' . tpl_getMediaFile($possibleLocation, $fallback = true) . '">';

        $possibleLocation = array(':wiki:favicon-16x16.png', ':favicon-16x16.png', 'images/favicon-16x16.png');
        $return .= '<link rel="icon" type="image/png" sizes="16x16" href="' . tpl_getMediaFile($possibleLocation, $fallback = true) . '">';

        // Apple touch icon
        $possibleLocation = array(':wiki:apple-touch-icon.png', ':apple-touch-icon.png', 'images/apple-touch-icon.png');
        $return .= '<link rel="apple-touch-icon" href="' . tpl_getMediaFile($possibleLocation, $fallback = true) . '" />' . NL;

        return $return;

    }

    static function renderPageTitle()
    {

        global $conf;
        global $ID;
        $title = tpl_pagetitle($ID, true) . ' [' . $conf["title"] . ']';
        // trigger event here
        Event::createAndTrigger('TPL_TITLE_OUTPUT', $title, '\ComboStrap\TplUtility::callBackPageTitle', true);
        return true;

    }

    /**
     * Print the title that we get back from the event TPL_TITLE_OUTPUT
     * triggered by the function {@link tpl_strap_title()}
     * @param $title
     */
    static function callBackPageTitle($title)
    {
        echo $title;
    }

    /**
     *
     * Set a template conf value
     *
     * To set a template configuration, you need to first load them
     * and there is no set function in template.php
     *
     * @param $confName - the configuration name
     * @param $confValue - the configuration value
     */
    static function setConf($confName, $confValue)
    {

        /**
         * Env variable
         */
        global $conf;
        $template = $conf['template'];

        if ($template != "strap") {
            throw new \RuntimeException("This is not the strap template, in test, active it in setup");
        }

        /**
         *  Make sure to load the configuration first by calling getConf
         */
        $actualValue = tpl_getConf($confName);
        if ($actualValue === false) {

            self::reloadConf();

            // Check that the conf was loaded
            if (tpl_getConf($confName) === false) {
                throw new \RuntimeException("The configuration (" . $confName . ") returns no value or has no default");
            }
        }

        $conf['tpl'][$template][$confName] = $confValue;

    }

    /**
     * When running multiple test, the function {@link tpl_getConf()}
     * does not reload the configuration twice
     */
    static function reloadConf()
    {
        /**
         * Env variable
         */
        global $conf;
        $template = $conf['template'];

        $tconf = tpl_loadConfig();
        if ($tconf !== false) {
            foreach ($tconf as $key => $value) {
                if (isset($conf['tpl'][$template][$key])) continue;
                $conf['tpl'][$template][$key] = $value;
            }
        }
    }


    /**
     * Send a message to a manager and log it
     * Fail if in test
     * @param string $message
     * @param int $level - the level see LVL constant
     * @param string $canonical - the canonical
     */
    static function msg($message, $level = self::LVL_MSG_ERROR, $canonical = null)
    {
        $strapUrl = self::getStrapUrl();
        $prefix = "<a href=\"$strapUrl\">Strap</a>";
        if ($canonical != null) {
            $prefix = '<a href="https://combostrap.com/' . $canonical . '">' . ucfirst($canonical) . '</a>';
        }
        $htmlMsg = $prefix . " - " . $message;
        if ($level != self::LVL_MSG_DEBUG) {
            msg($htmlMsg, $level, $allow = MSG_MANAGERS_ONLY);
        }
        /**
         * Print to a log file
         * Note: {@link dbg()} dbg print to the web page
         */
        $prefix = 'strap';
        if ($canonical != null) {
            $prefix .= ' - ' . $canonical;
        }
        $msg = $prefix . ' - ' . $message;
        dbglog($msg);
        if (defined('DOKU_UNITTEST') && ($level == self::LVL_MSG_WARNING || $level == self::LVL_MSG_ERROR)) {
            throw new \RuntimeException($msg);
        }
    }

    /**
     * @param bool $prependTOC
     * @return false|string - Adapted from {@link tpl_content()} to return the HTML
     */
    static function tpl_content($prependTOC = true)
    {
        global $ACT;
        global $INFO;
        $INFO['prependTOC'] = $prependTOC;

        ob_start();
        Event::createAndTrigger('TPL_ACT_RENDER', $ACT, 'tpl_content_core');
        $html_output = ob_get_clean();

        /**
         * The action null does nothing.
         * See {@link Event::trigger()}
         */
        Event::createAndTrigger('TPL_CONTENT_DISPLAY', $html_output, null);

        return $html_output;
    }

    static function getHeader()
    {

        $navBarPageName = tpl_getConf(self::CONF_HEADER);
        if (page_findnearest($navBarPageName)) {

            $header = tpl_include_page($navBarPageName, 0, 1);

        } else {

            $domain = self::getApexDomainUrl();
            $header = '<div class="container p-3" style="text-align: center">Welcome to the <a href="' . $domain . '/strap">Strap template</a>.</br>
            If you don\'t known the <a href="https://combostrap.com/strap">Strap template</a>, it\'s recommended to read the <a href="' . $domain . '/strap">introduction</a>.</br>
            Otherwise, to create a navigation bar, create a page with the id (' . html_wikilink(':' . $navBarPageName) . ') and the <a href="' . $domain . '/navbar">navbar component</a>.
            </div>';

        }
        return $header;

    }

    static function getFooter()
    {
        $domain = self::getApexDomainUrl();

        $footerPageName = tpl_getConf(self::CONF_FOOTER);
        if (page_findnearest($footerPageName)) {
            $footer = tpl_include_page($footerPageName, 0, 1);
        } else {
            $footer = '<div class="container p-3" style="text-align: center">Welcome to the <a href="' . $domain . '/strap">Strap template</a>. To get started, create a page with the id ' . html_wikilink(':' . $footerPageName) . ' to create a footer.</div>';
        }


        return $footer;
    }

    static function getPoweredBy()
    {

        $domain = self::getApexDomainUrl();
        $version = self::getVersion();
        $poweredBy = "<div class=\"mx-auto\" style=\"width: 300px;text-align: center;\">";
        $poweredBy .= "  <small><i>Powered by <a href=\"$domain\" title=\"ComboStrap " . $version . "\">ComboStrap</a></i></small>";
        $poweredBy .= '</div>';
        return $poweredBy;
    }


}


