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

use ComboStrap\TplConstant;

/**
 * Template footer, included in the main and detail files
 */

// must be run from within DokuWiki
if (!defined('DOKU_INC')) die();
?>


<?php
$domain = "https://combostrap.com";
$footerPageName = tpl_getConf(TplConstant::CONF_FOOTER);
if (page_findnearest($footerPageName)) {
    tpl_flush();
    tpl_include_page($footerPageName, 1, 1);
} else {
    echo '<div class="container p-3" style="text-align: center">Welcome to the <a href="' . $domain . '/strap">Strap template</a>. To get started, create a page with the id ' . html_wikilink(':' . $footerPageName) . ' to create a footer.</div>';
}

echo '<div class="mx-auto" style="width: 300px;text-align: center">';
echo '  <i>Powered by the <a href="https://combostrap.com/strap" title="Strap Template">Strap Template</a></i>';
echo '</div>';
?>




