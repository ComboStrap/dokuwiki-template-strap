<?php
/**
 * Custom Function for the template
 */

// must be run from within DokuWiki
use dokuwiki\Extension\Event;

const BOOTIE = 'strap';
if (!defined('DOKU_INC')) die();


/**
 * Print the breadcrumbs trace with Bootstrap class
 *
 * @param string $sep Separator between entries
 * @return bool
 * @author Nicolas GERARD
 *
 *
 */
function tpl_breadcrumbs_bootstrap($sep = '�')
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
function tpl_youarehere_bootstrap($printPrefix = false)
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

function tpl_pageName($id)
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

function tpl_searchform_strap($ajax = true, $autocomplete = true)
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
function tpl_actionlink_strap($type, $class = '', $pre = '', $suf = '', $inner = '', $return = false)
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
 * */
function tpl_get_default_headers()
{

    // The version
    $bootstrapVersion = tpl_getConf('bootstrapVersion');
    $scriptsMeta = getScripts($bootstrapVersion);

    // if cdn
    $useCdn = tpl_getConf('cdn');
    $urlKey = 'url_cdn';
    if (!$useCdn) {
        $urlKey = 'url_local';
    }

    // Build the returned Js script array
    $jsScripts = array();
    foreach ($scriptsMeta as $key => $script) {
        if ($script['type']==="js") {
            $jsScripts[$key] =
                array(
                    'src' => $script[$urlKey],
                    'integrity' => $script['integrity'],
                    'crossorigin' => $script['crossorigin'],
                    'defer' => $script['defer']
                );
        }
    }

    $css = array();
    foreach ($scriptsMeta as $key => $script) {
        if ($script['type']==="css") {
            $css[$key] =
                array(
                    'href' => $script[$urlKey],
                    'integrity' => $script['integrity'],
                    'crossorigin' => $script['crossorigin'],
                    'rel' => "stylesheet"
                );
        }
    }


    return array(
        'script' => $jsScripts,
        'link' => $css
    );


}

/**
 * @param $version
 * @return array
 *
 * jquery must not be slim because the post is needed for qsearch
 */
function getScripts($version){

    $localBaseJs = DOKU_BASE . 'lib/tpl/strap/js/' . $version;
    $localBaseCss = DOKU_BASE . 'lib/tpl/strap/css/' . $version;

    $scripts = array();
    if ($version == '4.4.1') {
        $scripts['jquery'] = array(
            'name' => 'jquery',
            'type' => 'js',
            'version' => '3.4.1',
            'url_cdn' => 'https://code.jquery.com/jquery-3.4.1.min.js',
            'url_local' => $localBaseJs.'/jquery-3.4.1.min.js',
            'integrity' => 'sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=',
            'crossorigin' => "anonymous",
            'defer' => "true"
        );
        $scripts['popper'] = array(
            'name' => 'popper',
            'type' => 'js',
            'version' => '1.16.0',
            'url_cdn' => 'https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js',
            'url_local' => $localBaseJs.'/popper.min.js',
            'integrity' => 'sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo',
            'crossorigin' => "anonymous",
            'defer' => "true"
        );
        $scripts['bootstrap'] = array(
            'name' => 'bootstrap',
            'type' => 'js',
            'version' => '4.4.1',
            'url_local' => $localBaseJs.'/bootstrap.min.js',
            'url_cdn' => 'https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js',
            'integrity' => "sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6",
            'crossorigin' => "anonymous",
            'defer' => "true"
        );
        $scripts['bootstrapCss'] = array(
            'name' => 'bootstrap',
            'type' => 'css',
            'version' => '4.4.1',
            'url_local' => $localBaseCss.'/bootstrap.min.css',
            'url_cdn' => 'https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css',
            'integrity' => "sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh",
            'crossorigin' => "anonymous"
        );
    }
    if ($version == '4.5.0'){
        $scripts['jquery'] = array(
            'name' => 'jquery',
            'type' => 'js',
            'version' => '3.5.1',
            'url_cdn' => 'https://code.jquery.com/jquery-3.5.1.min.js',
            'url_local' => $localBaseJs.'/jquery-3.5.1.min.js',
            'integrity' => 'sha384-ZvpUoO/+PpLXR1lu4jmpXWu80pZlYUAfxl5NsBMWOEPSjUn/6Z/hRTt8+pR6L4N2',
            'crossorigin' => "anonymous",
            'defer' => "true"
        );
        $scripts['popper'] = array(
            'name' => 'popper',
            'type' => 'js',
            'version' => '1.16.0',
            'url_cdn' => 'https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js',
            'url_local' => $localBaseJs.'/popper.min.js',
            'integrity' => 'sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo',
            'crossorigin' => "anonymous",
            'defer' => "true"
        );
        $scripts['bootstrap'] = array(
            'name' => 'bootstrap',
            'type' => 'js',
            'version' => '4.5.0',
            'url_local' => $localBaseJs.'/bootstrap.min.js',
            'url_cdn' => 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js',
            'integrity' => "sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI",
            'crossorigin' => "anonymous",
            'defer' => "true"
        );
        $scripts['bootstrapCss'] = array(
            'name' => 'bootstrap',
            'type' => 'css',
            'version' => '4.5.0',
            'url_local' => $localBaseCss.'/bootstrap.min.css',
            'url_cdn' => 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css',
            'integrity' => "sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk",
            'crossorigin' => "anonymous"
        );
    }

    return $scripts;
}
/**
 * @param Doku_Event $event
 * @param $param
 * Function that handle the META HEADER event
 *   * It will add the Bootstrap Js and CSS
 *   * Make all script and resources defer
 */
function tpl_strap_meta_header(Doku_Event &$event, $param)
{

    $debug = tpl_getConf('debug');
    if ($debug) {
        $request = 'Request: ' . json_encode($_REQUEST);
        print_r('<!-- ' . $request . '-->');
    }

    global $DOKU_TPL_BOOTIE_PRELOAD_CSS;
    $DOKU_TPL_BOOTIE_PRELOAD_CSS = array();

    $newHeaderTypes = array();
    $bootstrapHeaders = tpl_get_default_headers();
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
                $newLinkData = $bootstrapHeaders[$headerType]; // Css of Bootstrap will be unchanged
                foreach ($headerData as $linkData) {
                    switch ($linkData['rel']) {
                        case 'edit':
                            break;
                        case 'stylesheet':
//                             if ( strpos($linkData['href'],'css.php')){
//                                 continue;
//                             }
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
                $newHeaderTypes[$headerType] = $newLinkData;
                break;

            case "script":

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
function tpl_strap_favicon()
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

function tpl_strap_title_print()
{

    global $conf;
    global $ID;
    $title = tpl_pagetitle($ID, true) . ' ['. $conf["title"]. ']';
    // trigger event here
    Event::createAndTrigger('TPL_TITLE_OUTPUT', $title, '_tpl_strap_title_print', true);
    return true;

}

/**
 * Print the title that we get back from the event TPL_TITLE_OUTPUT
 * triggered by the function {@link tpl_strap_title()}
 * @param $title
 */
function _tpl_strap_title_print($title)
{
    echo "<title>$title</title>";
}

