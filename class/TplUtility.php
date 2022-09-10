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

use action_plugin_combo_snippetsbootstrap;
use Doku_Event;
use dokuwiki\Extension\Event;
use dokuwiki\Menu\PageMenu;
use dokuwiki\Menu\SiteMenu;
use dokuwiki\Menu\UserMenu;
use dokuwiki\plugin\config\core\Configuration;
use dokuwiki\plugin\config\core\Writer;
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


    const CONF_HEADER_SLOT_PAGE_NAME = "headerSlotPageName";

    const CONF_FOOTER_SLOT_PAGE_NAME = "footerSlotPageName";


    /**
     * @deprecated for  {@link Bootstrap::CONF_BOOTSTRAP_VERSION_STYLESHEET}
     */
    const CONF_BOOTSTRAP_VERSION = "bootstrapVersion";
    /**
     * @deprecated for  {@link Bootstrap::CONF_BOOTSTRAP_VERSION_STYLESHEET}
     */
    const CONF_BOOTSTRAP_STYLESHEET = "bootstrapStylesheet";

    /**
     * Deprecated
     */
    const CONF_GRID_COLUMNS = "gridColumns";

    // preload all css ?
    const BS_4_BOOTSTRAP_VERSION_STYLESHEET = "4.5.0 - bootstrap";

    /**
     * @deprecated
     */
    const CONF_SIDEKICK_OLD = "sidekickbar";
    const CONF_SIDEKICK_SLOT_PAGE_NAME = "sidekickSlotPageName";
    const CONF_SLOT_HEADER_PAGE_NAME_VALUE = "slot_header";

    /**
     * @deprecated see {@link TplUtility::CONF_HEADER_SLOT_PAGE_NAME}
     */
    const CONF_HEADER_OLD = "headerbar";
    /**
     * @deprecated
     */
    const CONF_HEADER_OLD_VALUE = TplUtility::CONF_HEADER_OLD;
    /**
     * @deprecated see {@link TplUtility::CONF_FOOTER_SLOT_PAGE_NAME}
     */
    const CONF_FOOTER_OLD = "footerbar";

    /**
     * A parameter switch to allows the update
     * of conf in test
     */
    const COMBO_TEST_UPDATE = "combo_update_conf";

    const SLOT_MAIN_FOOTER = "slot_main_footer";
    const SLOT_MAIN_HEADER = "slot_main_header";

    /**
     * @var array|null
     */
    private static $TEMPLATE_PLUGIN_INFO = null;
    private static $COMBO_PLUGIN_INFO;


    /**
     * @param string $text add a comment into the HTML page
     */
    private static function addAsHtmlComment($text)
    {
        print_r('<!-- TplUtility Comment: ' . hsc($text) . '-->');
    }

    public static function getApexDomainUrl()
    {
        return self::getTemplatePluginInfo()["url"];
    }

    public static function getTemplatePluginInfo(): array
    {
        if (self::$TEMPLATE_PLUGIN_INFO === null) {
            self::$TEMPLATE_PLUGIN_INFO = confToHash(__DIR__ . '/../template.info.txt');
        }
        return self::$TEMPLATE_PLUGIN_INFO;
    }

    public static function getComboPluginInfo(): array
    {
        if (self::$COMBO_PLUGIN_INFO === null) {
            self::$COMBO_PLUGIN_INFO = confToHash(__DIR__ . '/../../../plugins/combo/plugin.info.txt');
        }
        return self::$COMBO_PLUGIN_INFO;
    }

    public static function getFullQualifyVersion(): string
    {
        return "v" . self::getTemplatePluginInfo()['version'] . " (" . self::getTemplatePluginInfo()['date'] . ")";
    }


    private static function getStrapUrl()
    {
        return self::getTemplatePluginInfo()["strap"];
    }


    /**
     * @return mixed|string
     */
    public static function getMainSideSlotName()
    {
        $oldSideKickSlotName = TplUtility::migrateSlotConfAndGetValue(
            TplUtility::CONF_SIDEKICK_SLOT_PAGE_NAME,
            "slot_sidekick",
            TplUtility::CONF_SIDEKICK_OLD,
            "sidekickbar",
            "sidekick_slot"
        );
        if ($oldSideKickSlotName !== null) {
            return $oldSideKickSlotName;
        }
        return "slot_main_side";
    }

    public static function getHeaderSlotPageName()
    {

        return TplUtility::migrateSlotConfAndGetValue(
            TplUtility::CONF_HEADER_SLOT_PAGE_NAME,
            TplUtility::CONF_SLOT_HEADER_PAGE_NAME_VALUE,
            TplUtility::CONF_HEADER_OLD,
            TplUtility::CONF_HEADER_OLD_VALUE,
            "header_slot"
        );

    }

    public static function getFooterSlotPageName()
    {
        return self::migrateSlotConfAndGetValue(
            TplUtility::CONF_FOOTER_SLOT_PAGE_NAME,
            "slot_footer",
            TplUtility::CONF_FOOTER_OLD,
            "footerbar",
            "footer_slot"
        );
    }

    /**
     * @param string $key the key configuration
     * @param string $value the value
     * @return bool
     */
    public static function updateConfiguration($key, $value)
    {

        /**
         * Hack to avoid updating during {@link \TestRequest}
         * when not asked
         * Because the test request environment is wiped out only on the class level,
         * the class / test function needs to specifically say that it's open
         * to the modification of the configuration
         */
        global $_REQUEST;
        if (defined('DOKU_UNITTEST') && !isset($_REQUEST[self::COMBO_TEST_UPDATE])) {

            /**
             * This hack resolves two problems
             *
             * First one
             * this is a test request
             * the local.php file has a the `DOKU_TMP_DATA`
             * constant in the file and updating the file
             * with this method will then update the value of savedir to DOKU_TMP_DATA
             * we get then the error
             * The datadir ('pages') at DOKU_TMP_DATA/pages is not found
             *
             *
             * Second one
             * if in a php test unit, we send a php request two times
             * the headers have been already send and the
             * {@link msg()} function will send them
             * causing the {@link TplUtility::outputBuffer() output buffer check} to fail
             */
            global $MSG_shown;
            if (isset($MSG_shown) || headers_sent()) {
                return false;
            } else {
                return true;
            }

        }


        $configuration = new Configuration();
        $settings = $configuration->getSettings();

        $key = "tpl____strap____" . $key;
        if (isset($settings[$key])) {
            $setting = &$settings[$key];
            $setting->update($value);
            /**
             * We cannot update the setting
             * via the configuration object
             * We are taking another pass
             */

            $writer = new Writer();
            if (!$writer->isLocked()) {
                try {
                    $writer->save($settings);
                    return true;
                } catch (Exception $e) {
                    TplUtility::msg("An error occurred while trying to save automatically the configuration ($key) to the value ($value). Error: " . $e->getMessage());
                    return false;
                }
            } else {
                TplUtility::msg("The configuration file was locked. The upgrade configuration ($key) value could not be not changed to ($value)");
                return false;
            }

        } else {

            /**
             * When we run test,
             * strap is not always the active template
             * and therefore the configurations are not loaded
             */
            global $conf;
            if ($conf['template'] == TplUtility::TEMPLATE_NAME) {
                TplUtility::msg("The configuration ($key) is unknown and was therefore not change to ($value)");
            }
        }

        return false;


    }

    /**
     * Helper to migrate from bar to slot
     * @return mixed|string
     */
    public static function migrateSlotConfAndGetValue($newConf, $newDefaultValue, $oldConf, $oldDefaultValue, $canonical)
    {

        $name = tpl_getConf($newConf, null);
        if ($name == null) {
            $name = tpl_getConf($oldConf, null);
        }
        if ($name == null) {

            $foundOldName = false;
            if (page_exists($oldConf)) {
                $foundOldName = true;
            }

            if (!$foundOldName) {
                global $conf;
                $startPageName = $conf["start"];
                $startPagePath = wikiFN($startPageName);
                $directory = dirname($startPagePath);


                $childrenDirectories = glob($directory . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR);
                foreach ($childrenDirectories as $childrenDirectory) {
                    $directoryName = pathinfo($childrenDirectory)['filename'];
                    $dokuFilePath = $directoryName . ":" . $oldDefaultValue;
                    if (page_exists($dokuFilePath)) {
                        $foundOldName = true;
                        break;
                    }
                }
            }

            if ($foundOldName) {
                $name = $oldDefaultValue;
            } else {
                $name = $newDefaultValue;
            }
            $updated = TplUtility::updateConfiguration($newConf, $name);
            if ($updated) {
                TplUtility::msg("The <a href=\"https://combostrap.com/$canonical\">$newConf</a> configuration was set with the value <mark>$name</mark>", self::LVL_MSG_INFO, $canonical);
            }
        }
        return $name;
    }

    /**
     * Output buffer checks
     *
     * It should be null before printing otherwise
     * you may get a text before the HTML header
     * and it mess up the whole page
     */
    public static function outputBuffer()
    {
        $length = ob_get_length();
        $ob = "";
        if ($length > 0) {
            $ob = ob_get_contents();
            ob_clean();
            global $ACT;
            if ($ACT === "show") {
                /**
                 * If you got this problem check that this is not a character before a  `<?php` declaration
                 */
                TplUtility::msg("A plugin has send text before the creation of the page. Because it will mess the rendering, we have deleted it. The content was: (" . $ob . ")", TplUtility::LVL_MSG_ERROR, "strap");
            }
        }
        return $ob;

    }


    /**
     * @return string
     * Railbar items can add snippet in the head
     * And should then be could before the HTML output
     *
     * In Google Material Design, they call it a
     * navigational drawer
     * https://material.io/components/navigation-drawer
     */
    public static function getRailBar($breakpoint = null): string
    {


        try {
            self::checkSameStrapAndComboVersion();

            if (tpl_getConf(FetcherRailBar::CONF_PRIVATE_RAIL_BAR) === 1 && empty($_SERVER['REMOTE_USER'])) {
                return "";
            }

            if ($breakpoint === null) {
                $breakpoint = tpl_getConf(FetcherRailBar::CONF_BREAKPOINT_RAIL_BAR, Breakpoint::BREAKPOINT_LARGE_NAME);
            }

            $bootstrapBreakpoint = "";
            switch ($breakpoint) {
                case Breakpoint::EXTRA_SMALL_NAME:
                    $bootstrapBreakpoint = "xs";
                    break;
                case Breakpoint::BREAKPOINT_SMALL_NAME:
                    $bootstrapBreakpoint = "sm";
                    break;
                case Breakpoint::BREAKPOINT_MEDIUM_NAME:
                    $bootstrapBreakpoint = "md";
                    break;
                case Breakpoint::BREAKPOINT_LARGE_NAME:
                    $bootstrapBreakpoint = "lg";
                    break;
                case Breakpoint::BREAKPOINT_EXTRA_LARGE_NAME:
                    $bootstrapBreakpoint = "xl";
                    break;
                case Breakpoint::EXTRA_EXTRA_LARGE_NAME:
                    $bootstrapBreakpoint = "xxl";
                    break;
            }
        } catch (Exception $e) {
            //
        }


        $classOffCanvas = "";
        $classFixed = "";
        if (!empty($bootstrapBreakpoint)) {
            $classOffCanvas = "class=\"d-$bootstrapBreakpoint-none d-print-none\"";
            $classFixed = "class=\"d-none d-$bootstrapBreakpoint-flex d-print-none\"";
        }

        $railBarListItems = TplUtility::getRailBarListItems();
        $railBarOffCanvas = <<<EOF
<div id="railbar-offcanvas-wrapper" $classOffCanvas>
    <button id="railbar-offcanvas-open" class="btn" type="button" data-bs-toggle="offcanvas"
            data-bs-target="#railbar-offcanvas" aria-controls="railbar-offcanvas">
    </button>

    <div id="railbar-offcanvas" class="offcanvas offcanvas-end" tabindex="-1"
         aria-labelledby="offcanvas-label"
         style="visibility: hidden;" aria-hidden="true">
         <h5 class="d-none" id="offcanvas-label">Railbar</h5>
        <!-- Pseudo relative element  https://stackoverflow.com/questions/6040005/relatively-position-an-element-without-it-taking-up-space-in-document-flow -->
        <div style="position: relative; width: 0; height: 0">
            <button id="railbar-offcanvas-close" class="btn" type="button" data-bs-dismiss="offcanvas"
                    aria-label="Close">
            </button>
        </div>
        <div id="railbar-offcanvas-body" class="offcanvas-body" style="align-items: center;display: flex;">
            $railBarListItems
        </div>
    </div>
</div>
EOF;

        if ($breakpoint === "never") {
            return $railBarOffCanvas;
        }

        $zIndexRailbar = 1000; // A navigation bar (below the drop down because we use it in the search box for auto-completion)
        $railBarFixed = <<<EOF
<div id="railbar-fixed" style="z-index: $zIndexRailbar;" $classFixed>
    <div>
        $railBarListItems
    </div>
</div>
EOF;
        return <<<EOF
$railBarOffCanvas
$railBarFixed
EOF;

    }

    /**
     *
     * https://material.io/components/navigation-rail|Navigation rail
     * @return string - the ul part of the railbar
     */
    public
    static function getRailBarListItems(): string
    {
        $liUserTools = (new UserMenu())->getListItems('action');
        $pageMenu = new PageMenu();
        $liPageTools = $pageMenu->getListItems();
        $liSiteTools = (new SiteMenu())->getListItems('action');
        // FYI: The below code outputs all menu in mobile (in another HTML layout)
        // echo (new \dokuwiki\Menu\MobileMenu())->getDropdown($lang['tools']);
        return <<<EOF
<ul class="railbar">
    <li><a href="#" style="height: 19px;line-height: 17px;text-align: left;font-weight:bold"><span>User</span><svg style="height:19px"></svg></a></li>
    $liUserTools
    <li><a href="#" style="height: 19px;line-height: 17px;text-align: left;font-weight:bold"><span>Page</span><svg style="height:19px"></svg></a></li>
    $liPageTools
    <li><a href="#" style="height: 19px;line-height: 17px;text-align: left;font-weight:bold"><span>Website</span><svg style="height:19px"></svg></a></li>
    $liSiteTools
</ul>
EOF;

    }


    public static function getSideSlotPageName()
    {
        global $conf;
        return $conf['sidebar'];
    }

    public static function isNotRootHome(): bool
    {
        global $ID;
        global $conf;
        $startName = $conf['start'];
        return $ID !== $startName;
    }

    public static function getRem()
    {
        try {
            self::checkSameStrapAndComboVersion();
            return tpl_getConf(PageLayout::CONF_REM_SIZE, null);
        } catch (Exception $e) {
            return null;
        }

    }


    /**
     * @throws Exception
     */
    public static function checkSameStrapAndComboVersion()
    {
        /**
         * Check the version
         */
        $templateVersion = TplUtility::getTemplatePluginInfo()['version'];
        $comboVersion = TplUtility::getComboPluginInfo()['version'];
        /** @noinspection DuplicatedCode */
        if ($templateVersion !== $comboVersion) {
            $strapName = "Strap";
            $comboName = "Combo";
            $strapLink = "<a href=\"https://www.dokuwiki.org/template:strap\">$strapName</a>";
            $comboLink = "<a href=\"https://www.dokuwiki.org/plugin:combo\">$comboName</a>";
            if ($comboVersion > $templateVersion) {
                $upgradeTarget = $strapName;
            } else {
                $upgradeTarget = $comboName;
            }
            $upgradeLink = "<a href=\"" . wl() . "&do=admin&page=extension" . "\">upgrade <b>$upgradeTarget</b> via the extension manager</a>";
            $message = "You should $upgradeLink to the latest version to get a fully functional experience. The version of $comboLink is ($comboVersion) while the version of $strapLink is ($templateVersion).";

            throw new Exception($message);
        }

        // may be disabled
//        global $plugin_controller;
//        if (!$plugin_controller->isEnabled("combo")) {
//            throw new Exception("Combo is disabled");
//        }

        /**
         * Loading all combo classes
         */
        $filename = DOKU_PLUGIN . "combo/vendor/autoload.php";
        if (!file_exists($filename)) {
            throw new \Exception("Internal Error: Combo autoload was not found. Combo is installed ?");
        }
        require_once($filename);

    }

    /**
     * Copy of {@link Identity::isManager()}
     */
    private static function isManager()
    {
        global $INFO;
        if ($INFO !== null) {
            return $INFO['ismanager'];
        } else {
            /**
             * In test
             */
            return auth_ismanager();
        }
    }

    /**
     * Variation of {@link html_msgarea()}
     * @return string
     */
    public static function printMessage(): string
    {

        global $MSG, $MSG_shown;
        /** @var array $MSG */
        // store if the global $MSG has already been shown and thus HTML output has been started
        $MSG_shown = true;

        if (!isset($MSG)) return "";


        $shown = array();

        $toasts = "";
        foreach ($MSG as $msg) {
            $hash = md5($msg['msg']);
            if (isset($shown[$hash])) continue; // skip double messages
            if (info_msg_allowed($msg)) {
                $level = ucfirst($msg['lvl']);
                switch ($level) {
                    case "Error":
                        $class = "text-danger";
                        $autoHide = "false";
                        break;
                    default:
                        $class = "text-primary";
                        $autoHide = "true";
                        break;
                }
                $toasts .= <<<EOF
<div role="alert" aria-live="assertive" aria-atomic="true" class="toast fade" data-bs-autohide="$autoHide">
  <div class="toast-header">
    <strong class="me-auto $class">{$level}</strong>
    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
  </div>
  <div class="toast-body">
        <p>{$msg['msg']}</p>
  </div>
</div>
EOF;

            }
            $shown[$hash] = 1;
        }

        unset($GLOBALS['MSG']);

        if ($toasts === "") {
            return "";
        }

        // position fixed to not participate into the grid
        return <<<EOF
<div class="toast-container position-fixed mb-3 me-3 bottom-0 end-0" id="toastPlacement" style="z-index:1060">
$toasts
</div>

<script>
window.addEventListener("DOMContentLoaded",function(){
    const toastElements = [].slice.call(document.querySelectorAll('.toast'));
    toastElements.map(function (toastElement) {
      let toast = new bootstrap.Toast(toastElement);
      toast.show();
      if(toastElement.dataset.bsAutohide==="false"){
          toastElement.querySelector("button").focus();
      }
    });
});
</script>
EOF;
    }


    public
    static function isNotSlot(): bool
    {
        global $ID;
        return strpos($ID, TplUtility::getSideSlotPageName()) === false
            && strpos($ID, TplUtility::getMainSideSlotName()) === false
            && strpos($ID, self::SLOT_MAIN_FOOTER) === false
            && (strpos($ID, self::SLOT_MAIN_HEADER) === false)
            && strpos($ID, TplUtility::getHeaderSlotPageName()) === false
            && strpos($ID, TplUtility::getFooterSlotPageName()) === false;
    }

    public
    static function getXhtmlForSlotName($slotName)
    {
        $nearestWikiId = page_findnearest($slotName);
        if ($nearestWikiId === false) {
            return "";
        }
        return tpl_include_page($nearestWikiId, 0, 1);
    }

    public
    static function isTest(): bool
    {
        return defined('DOKU_UNITTEST');
    }

    /**
     * Page footer use in main.php and detail.php
     * @return string
     */
    public static function getPageFooter(): string
    {
        try {
            return TplUtility::getXhtmlForSlotName(TplUtility::getFooterSlotPageName());
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Page header use in main.php and detail.php
     * @return string
     */
    public static function getPageHeader(): string
    {
        try {
            return TplUtility::getXhtmlForSlotName(TplUtility::getHeaderSlotPageName());
        } catch (Exception $e) {
            return $e->getMessage();
        }
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
        if (!$conf['youarehere']) return "";

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
        $return .= '<link rel="shortcut icon" href="' . tpl_getMediaFile($possibleLocation, true) . '" />' . NL;

        // Icon Png
        $possibleLocation = array(':wiki:favicon-32x32.png', ':favicon-32x32.png', 'images/favicon-32x32.png');
        $return .= '<link rel="icon" type="image/png" sizes="32x32" href="' . tpl_getMediaFile($possibleLocation, true) . '"/>';

        $possibleLocation = array(':wiki:favicon-16x16.png', ':favicon-16x16.png', 'images/favicon-16x16.png');
        $return .= '<link rel="icon" type="image/png" sizes="16x16" href="' . tpl_getMediaFile($possibleLocation, true) . '"/>';

        // Apple touch icon
        $possibleLocation = array(':wiki:apple-touch-icon.png', ':apple-touch-icon.png', 'images/apple-touch-icon.png');
        $return .= '<link rel="apple-touch-icon" href="' . tpl_getMediaFile($possibleLocation, true) . '" />' . NL;

        return $return;

    }

    static function getPageTitle(): string
    {

        global $conf;
        global $ID;
        $title = tpl_pagetitle($ID, true) . ' | ' . $conf["title"];
        // trigger event here
        Event::createAndTrigger('TPL_TITLE_OUTPUT', $title);
        return $title;

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
    static function msg($message, $level = self::LVL_MSG_ERROR, $canonical = "strap")
    {
        $strapUrl = self::getStrapUrl();
        $prefix = "<a href=\"$strapUrl\">Strap</a>";
        $prefix = '<a href="https://combostrap.com/' . $canonical . '">' . ucfirst($canonical) . '</a>';

        $htmlMsg = $prefix . " - " . $message;
        if ($level != self::LVL_MSG_DEBUG) {
            msg($htmlMsg, $level, '', '', MSG_MANAGERS_ONLY);
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
     * Call {@link html_show()} from {@link Show::tplContent()}
     */
    static function tpl_content(bool $prependTOC = true)
    {
        global $ACT;
        global $REV;
        global $DATE_AT;

        global $INFO;
        $INFO['prependTOC'] = $prependTOC;


        $comboSameVersionThanStrap = true;
        try {
            TplUtility::checkSameStrapAndComboVersion();
        } catch (Exception $e) {
            $comboSameVersionThanStrap = false;
        }
        $showViaCombo = $ACT === "show" // show only
            && ($REV === 0 && $DATE_AT === "") // ro revisions
            && $comboSameVersionThanStrap;
        try {
            ob_start();
            if ($showViaCombo) {

                /**
                 * The code below replace the other block
                 * to take the snippet management into account
                 * (ie we write them when the {@link  MarkupPath::storeOutputContent() document is stored into cache)
                 */
                global $ID;
                /**
                 * The action null does nothing.
                 * See {@link Event::trigger()}
                 */
                Event::createAndTrigger('TPL_ACT_RENDER', $ACT, null);
                /**
                 * In the tpl_act_render, plugin has no other option
                 * than to output in the buffer
                 * https://www.dokuwiki.org/devel:event:tpl_act_render
                 * We take the buffer
                 */
                $html_output = ob_get_contents();
                ob_clean(); // delete the content to not have it twice after the rendering

                /**
                 * The code below replace {@link html_show()}
                 */
                $html_output .= MarkupPath::createMarkupFromId($ID)
                    ->toXhtml();
                $html_output = EditButton::replaceOrDeleteAll($html_output);


                /**
                 * Add the buffer (eventually)
                 *
                 * Not needed with our code, may be with other plugins, it should not as the
                 * syntax plugin should use the {@link \Doku_Renderer::$doc)
                 *
                 */
                $html_output .= ob_get_contents();
            } else {
                Event::createAndTrigger('TPL_ACT_RENDER', $ACT, 'tpl_content_core');
                $html_output = ob_get_contents();
            }
            ob_end_clean();

            /**
             * The action null does nothing.
             * See {@link Event::trigger()}
             */
            Event::createAndTrigger('TPL_CONTENT_DISPLAY', $html_output, null);

            return $html_output;
        } catch (Exception $e) {
            $prefix = "Unfortunately, an error has occurred during the rendering of the main content.";
            $class = get_class($e);
            $message = $e->getMessage();
            $trace = $e->getTraceAsString();
            LogUtility::log2file("$prefix. Error: $message, $class: $trace");

            /**
             * To the HTML page
             */
            if (TplUtility::isManager()) {
                return "$prefix <br/> <br/> Error (only seen by manager): <br/>$message ($class) <br/>" . str_replace("\n", "<br/>", $trace);
            } else {
                return "$prefix <br/> The error was logged in the log file. Errors are only visible by managers";
            }

        }
    }


}


