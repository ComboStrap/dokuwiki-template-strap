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

use ComboStrap\Bootstrap;
use ComboStrap\FetcherRailBar;
use ComboStrap\Snippet;
use ComboStrap\SnippetSystem;
use ComboStrap\TplUtility;

/**
 * The default value don't use false but 1
 * if you want to use an on/off
 * because false is the value returned when no configuration is found
 */



/**
 * CDN for anonymous
 * See {@link Snippet::CONF_USE_CDN}
 */
$conf['useCDN'] = 1;

// Print Debug statement
$conf['debug'] = 1;


/**
 * {@link Bootstrap::CONF_BOOTSTRAP_VERSION_STYLESHEET}
 */
$conf["bootstrapVersionStylesheet"] = "5.0.1 - bootstrap";

$conf['gridColumns'] = 12;

$conf['gridColumns'] = 12;


$conf['preloadCss'] = 0;

$conf['preloadCss'] = 0;

/**
 * {@link FetcherRailBar::CONF_PRIVATE_RAIL_BAR}
 */
$conf['privateRailbar'] = 0;
/**
 * {@link FetcherRailBar::CONF_BREAKPOINT_RAIL_BAR}
 */
$conf['breakpointRailbar'] = "large";


/**
 * See {@link action_plugin_combo_snippetsbootstrap::CONF_DISABLE_BACKEND_JAVASCRIPT}
 */
$conf["disableBackendJavascript"] = 0;


?>
