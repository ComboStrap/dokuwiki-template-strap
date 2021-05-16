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
require_once(__DIR__.'/../../class/TplUtility.php');


use ComboStrap\TplUtility;

$lang['debug'] = 'Enable the rendering / processing of debug information';

// for the configuration manager
$lang[TplUtility::CONF_FOOTER] = '<a href="https://combostrap.com/footerbor">Footer bar</a> - The name of the footer page to search';
$lang[TplUtility::CONF_HEADER] = '<a href="https://combostrap.com/headerbor">Header bar</a> - The name of the header page to search';
$lang[TplUtility::CONF_SIDEKICK] = '<a href="https://combostrap.com/sidekickbor">Sidekick bar</a> - The name of the right sidebar page';

$lang[TplUtility::CONF_USE_CDN] = '<a href="https://combostrap.com/cdn">CDN</a> - Use a frontend CDN for the Bootstrap files';

$lang[TplUtility::CONF_REM_SIZE] = '<a href="https://combostrap.com/length/scale">Length Scale</a> - This configuration define in pixels the value of 1 rem';



$lang[TplUtility::CONF_GRID_COLUMNS] = '<a href="https://combostrap.com/dynamic/grid">Dynamic Grid</a> - The number of columns in the grid';


$lang[TplUtility::CONF_HEIGHT_FIXED_TOP_NAVBAR] = '<a href="https://combostrap.com/top/navbar">Fixed-top Navbar</a> - The height of the top bar in pixel (40px normally)';

$lang['preloadCss'] = '<a href="https://combostrap.com/css#preloadCSS">CSS Optimization</a> - Enable CSS Preloading';
$lang['privateToolbar'] = '<a href="https://combostrap.com/toolbar">Toolbar</a> - Enable private toolbar';

$lang[TplUtility::CONF_BOOTSTRAP_VERSION_STYLESHEET] = '<a href="https://combostrap.com/bootstrap">Bootstrap</a> - The Bootstrap version and a corresponding stylesheet';

$lang[TplUtility::CONF_JQUERY_DOKU] = '<a href="https://combostrap.com/jquery">Jquery</a> - Use the DokuWiki Jquery';

?>
