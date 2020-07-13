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
use dokuwiki\Extension\Event;


use DateTime;
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

        $crumbs = array_reverse(breadcrumbs()); //setup crumb trace

        echo '<nav aria-label="breadcrumb">';

        $last = count($crumbs);
        $i = 0;
        // Try to get the template custom breadcrumb
        $breadCrumb = tpl_getLang('breadcrumb');
        if ($breadCrumb == '') {
            // If not present for the language, get the default one
            $breadCrumb = $lang['breadcrumb'];
        }

        echo '<div id="breadcrumb">' . PHP_EOL;
        echo '<span id="breadCrumbTitle" ">' . $breadCrumb . ':   </span>' . PHP_EOL;
        echo '<ol class="breadcrumb justify-content-start m-0 p-0 pb-1">' . PHP_EOL;

        foreach ($crumbs as $id => $name) {
            $i++;

            if ($i == $last) {
                print '<li class="breadcrumb-item active">';
            } else {
                print '<li class="breadcrumb-item">';
            }
            if ($name == "start") {
                $name = "Home";
            }
            tpl_link(wl($id), hsc($name), 'title="' . $name . '" style="width: 100%;"');

            print '</li>' . PHP_EOL;

        }
        echo '</ol>' . PHP_EOL;
        echo '</div>' . PHP_EOL;
        echo '</nav>' . PHP_EOL;
        return true;
    }

    /**
     * Return the padding top in pixel
     * @return int
     */
    public static function getPaddingTop()
    {
        // The padding top for the top fix bar
        $paddingTop = 0;
        $heightTopBar = tpl_getConf('heightTopBar',0);
        if ($heightTopBar!=0){
            $paddingTop = $heightTopBar + 30;
        }
        return $paddingTop;
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
        $bootstrapVersion = tpl_getConf('bootstrapVersion');
        if ($bootstrapVersion === false) {
            throw new Exception("Bootstrap version should not be false");
        }
        $scriptsMeta = self::buildBootstrapMetas($bootstrapVersion);

        // if cdn
        $useCdn = tpl_getConf('cdn');


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
                        'defer' => true
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
     * @return array - A list of the file name in the custom JSON file
     * This function is used to build the configuration as a list of files
     */
    static function getCustomCssFiles()
    {
        $cssVersionsMetas = self::getCustomCssMeta();
        $cssFiles = array();
        foreach ($cssVersionsMetas as $cssVersionMeta) {
            foreach ($cssVersionMeta as $fileName => $values) {
                $cssFiles[$fileName] = $fileName;
            }
        }
        return $cssFiles;
    }

    /**
     *
     * @param $version - return only the selected version if set
     * @return void - an array of the meta JSON custom files
     */
    static function getCustomCssMeta($version = null)
    {

        $jsonAsArray = true;
        $bootstrapCustomJsonFile = __DIR__ . '/bootstrap/bootstrapCustomCss.json';
        $bootstrapCustomMetas = json_decode(file_get_contents($bootstrapCustomJsonFile), $jsonAsArray);
        if ($bootstrapCustomMetas == null) {
            tpl_strap_msg("Unable to read the file {$bootstrapCustomJsonFile} as json");
        }
        $bootstrapLocalJsonFile = __DIR__ . '/bootstrap/bootstrapLocal.json';
        if (file_exists($bootstrapLocalJsonFile)) {
            $bootstrapLocalMetas = json_decode(file_get_contents($bootstrapLocalJsonFile), $jsonAsArray);
            if ($bootstrapLocalMetas == null) {
                tpl_strap_msg("Unable to read the file {$bootstrapLocalMetas} as json");
            }
            $bootstrapCustomMetas = array_merge($bootstrapCustomMetas, $bootstrapLocalMetas);
        }

        if (isset($version)) {
            if (!isset($bootstrapCustomMetas[$version])) {
                tpl_strap_msg("The bootstrap version ($version) could not be found in the custom CSS file ($bootstrapCustomJsonFile, or $bootstrapLocalJsonFile)");
            } else {
                $bootstrapCustomMetas = $bootstrapCustomMetas[$version];
            }
        }
        return $bootstrapCustomMetas;
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
        $bootstrapJsonFile = __DIR__ . '/bootstrap/bootstrap.json';
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
        $bootstrapCssFile = tpl_getConf('bootstrapCssFile');
        if ($bootstrapCssFile != "bootstrap.min.css") {

            $bootstrapCustomMetas = self::getCustomCssMeta($version);

            if (!isset($bootstrapCustomMetas[$bootstrapCssFile])) {
                self::msg("The bootstrap custom file ($bootstrapCssFile) could not be found in the custom CSS files for the version ($version)");
            } else {
                $bootstrapMetas['css'] = $bootstrapCustomMetas[$bootstrapCssFile];
            }

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
            $request = 'Request: ' . json_encode($_REQUEST);
            print_r('<!-- ' . $request . '-->');
        }


        $newHeaderTypes = array();
        $bootstrapHeaders = self::getBootstrapMetaHeaders();
        $eventHeaderTypes = $event->data;
        foreach ($eventHeaderTypes as $headerType => $headerData) {
            switch ($headerType) {
                case "meta":
                    // generator, color, robots, keywords
                    // nothing to do pick them all
                    $newHeaderTypes[$headerType] = $headerData;
                    break;
                case "link":
                    // index, rss, manifest, search, alternate, stylesheet
                    // delete edit
                    $bootstrapCss = $bootstrapHeaders[$headerType]['css'];
                    $headerData[] = $bootstrapCss;
                    $cssPreload = tpl_getConf("preloadCss");
                    $newLinkData = array();
                    if ($cssPreload) {

                        foreach ($headerData as $linkData) {
                            switch ($linkData['rel']) {
                                case 'edit':
                                    break;
                                case 'stylesheet':

                                    // Take the stylesheet to load them at the end
                                    $DOKU_TPL_BOOTIE_PRELOAD_CSS[] = $linkData;

                                    // Change the loading mechanism to preload
                                    $linkData['rel'] = 'preload';
                                    $linkData['as'] = 'style';
                                    $newLinkData[] = $linkData;

                                    break;
                                default:
                                    $newLinkData[] = $linkData;
                                    break;
                            }
                        }
                    } else {
                        $newLinkData = $headerData;
                    }
                    $newHeaderTypes[$headerType] = $newLinkData;
                    break;

                case
                "script":

                    $newScriptData = array();
                    // A variable to hold the Jquery scripts
                    // jquery-migrate, jquery, jquery-ui ou jquery.php
                    // see https://www.dokuwiki.org/config:jquerycdn
                    $jqueryDokuScripts = array();
                    foreach ($headerData as $scriptData) {
                        $scriptData['defer'] = "true";
                        $pos = strpos($scriptData['src'], 'jquery');
                        if ($pos === false) {
                            $newScriptData[] = $scriptData;
                        } else {
                            $jqueryDokuScripts[] = $scriptData;
                        }
                    }

                    // Add Jquery at the beginning
                    if (empty($_SERVER['REMOTE_USER'])) {
                        // We take the Jquery of Bootstrap
                        $newScriptData = array_merge($bootstrapHeaders[$headerType], $newScriptData);
                    } else {
                        // Logged in
                        // We take the Jqueries of doku and we add Bootstrap
                        $newScriptData = array_merge($jqueryDokuScripts, $newScriptData); // js
                        $newScriptData[] = $bootstrapHeaders[$headerType]['popper'];
                        $newScriptData[] = $bootstrapHeaders[$headerType]['bootstrap'];
                    }


                    $newHeaderTypes[$headerType] = $newScriptData;
                    break;

            }
        }

        if ($debug) {
            print_r('<!-- ' . 'Script Header : ' . json_encode($newHeaderTypes['script']) . '-->');
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
        echo "<title>$title</title>";
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
     * @throws Exception
     */
    static function setConf($confName, $confValue)
    {

        /**
         * Env variable
         */
        global $conf;
        $template = $conf['template'];

        /**
         *  Make sure to load the configuration first by calling getConf
         */
        $actualValue = tpl_getConf($confName);
        if ($actualValue === false) {

            self::reloadConf();

            // Check that the conf was loaded
            if (tpl_getConf($confName) === false) {
                throw new Exception("The configuration (" . $confName . ") returns no value");
            }
        }

        $conf['tpl'][$template][$confName] = $confValue;

    }

    /**
     * When running multiple test, the function {@link tpl_getConf()}
     * does not load the configuration twice
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
    function msg($message, $level = self::LVL_MSG_ERROR, $canonical = null)
    {
        $prefix = '<a href="https://combostrap.com/strap">Strap</a>';
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
}


