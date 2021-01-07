<?php
/**
 * Template header, included in the files main.php and detail.php
 */

// must be run from within DokuWiki
use ComboStrap\TplConstant;

if (!defined('DOKU_INC')) die();

global $conf;

?>

<!--header node-->
<!--transition: all .2s ease-->
<!--position: absolute;-->
<!--top: 0;-->
<!--left: 0;-->
<!--width: 100%;-->

<?php
$navBarPageName = tpl_getConf(TplConstant::CONF_HEADER);
if (page_findnearest($navBarPageName)) {
    tpl_flush();
    tpl_include_page($navBarPageName, 1, 1);

//    // Breadcrumb is here to be able to have a large introduction text
//    echo '<nav class="container">';
//    if ($conf['breadcrumbs']) {
//        tpl_breadcrumbs_bootstrap();
//    }
//    echo '</nav>';

} else {

    $domain = 'https://combostrap.com';
    echo '<div class="container p-3" style="text-align: center">Welcome to the <a href="' . $domain . '/strap">Strap template</a>.</br>
            If you don\'t known the <a href="https://combostrap.com/strap">Strap template</a>, it\'s recommended to read the <a href="' . $domain . '/strap">introduction</a>.</br>
            Otherwise, to create a navigation bar, create a page with the id (' . html_wikilink(':' . $navBarPageName) . ') and the <a href="' . $domain . '/navbar">navbar component</a>.
            </div>';

}
?>
