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


use ComboStrap\Bootstrap;
use ComboStrap\FetcherRailBar;
use ComboStrap\PageLayout;
use ComboStrap\Snippet;
use ComboStrap\SnippetSystem;
use ComboStrap\TplUtility;

$lang['debug'] = 'Enable the rendering / processing of debug information';

// for the configuration manager
$lang[TplUtility::CONF_SIDEKICK_SLOT_PAGE_NAME] = '<a href="https://combostrap.com/sidekick_slot">Sidekick Slot</a> - The name of the page to search for the sidekick slot (right side)';
$lang[TplUtility::CONF_FOOTER_SLOT_PAGE_NAME] = '<a href="https://combostrap.com/footer_slot">Footer Slot</a> - The name of the page to search for the footer page slot';
$lang[TplUtility::CONF_HEADER_SLOT_PAGE_NAME] = '<a href="https://combostrap.com/header_slot">Header Slot</a> - The name of the page to search for the header page slot';

$lang[Snippet::CONF_USE_CDN] = '<a href="https://combostrap.com/cdn">CDN</a> - Use a frontend CDN for the Bootstrap files';

$lang[PageLayout::CONF_REM_SIZE] = '<a href="https://combostrap.com/length/scale">Length Scale</a> - This configuration define in pixels the value of 1 rem';



$lang[TplUtility::CONF_GRID_COLUMNS] = '<a href="https://combostrap.com/dynamic/grid">Dynamic Grid</a> - The number of columns in the grid';


$lang['preloadCss'] = '<a href="https://combostrap.com/css#preloadCSS">CSS Optimization</a> - Enable CSS Preloading (!The page rendering will flash - FOUC)';




?>
