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

/**
 * The default value don't use false but 1
 * if you want to use an on/off
 * because false is the value returned when no configuration is found
 */


/**
 * The name of the footer page to search
 * See {@link TplConstant::CONF_FOOTER}
 */
$conf['footerbar'] = 'footerbar';
/**
 * The name of the header page to search
 * See {@link TplConstant::CONF_HEADER}
 */
$conf['headerbar'] = 'headerbar';
/**
 * The name of the header page to search
 * See {@link TplConstant::CONF_SIDEKICK}
 */
$conf['sidekickbar'] = 'sideckickbar';

/**
 * CDN for anonymous
 * See {@link TplConstant::CONF_USE_CDN}
 */
$conf['useCDN'] = 1;

// Print Debug statement
$conf['debug'] = 1;

$conf['remSize']= "16";

$conf['bootstrapVersion'] = '4.5.0';
$conf['bootstrapStylesheet']="bootstrap.min.css";

$conf['gridColumns'] = 12;

/**
 * The height of the navbar when
 * it's fixed in order to calculate the needed style
 * @see {@link \ComboStrap\TplConstant::CONF_HEIGHT_FIXED_TOP_NAVBAR
 */
$conf['heightFixedTopNavbar'] = 0;

$conf['preloadCss'] = 0;

$conf['preloadCss'] = 0;

$conf['privateToolbar'] = 0;





?>
