<?php

//configuration metadata describe properties of the settings as used by the Configuration Manager
//https://www.dokuwiki.org/devel:configuration#configuration_metadata

require_once(__DIR__ . '/../class/TplUtility.php');


try {
    /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
    \ComboStrap\TplUtility::checkSameStrapAndComboVersion();
} catch (Exception $e) {
    return;
}

use ComboStrap\Bootstrap;
use ComboStrap\PageLayout;
use ComboStrap\Snippet;
use ComboStrap\TplUtility;

$meta[TplUtility::CONF_FOOTER_SLOT_PAGE_NAME] = array('string',
    "_caution" => "warning", // Show a warning
    "_pattern" => "/[a-zA-Z0-9]*/" // Only Accept alphanumeric characters
);

$meta[TplUtility::CONF_HEADER_SLOT_PAGE_NAME] = array('string',
    "_caution" => "warning", // Show a warning
    "_pattern" => "/[a-zA-Z0-9]*/" // Only Accept alphanumeric characters
);

$meta[TplUtility::CONF_SIDEKICK_SLOT_PAGE_NAME] = array('string',
    "_caution" => "warning", // Show a warning
    "_pattern" => "/[a-zA-Z0-9]*/" // Only Accept alphanumeric characters
);


/**
 * Do we use CDN when possible
 */
$meta[Snippet::CONF_USE_CDN] = array('onoff');

/**
 * Do we print debug statement
 */
$meta['debug'] = array('onoff');

/**
 * The size of 1 rem in pixel
 */
$meta[PageLayout::CONF_REM_SIZE] = array('string');


$meta[TplUtility::CONF_GRID_COLUMNS] = array('multichoice', '_choices' => array('12', '16'));

$meta['preloadCss'] = array('onoff');


$cssFiles = Bootstrap::getQualifiedVersions();
$meta[Bootstrap::CONF_BOOTSTRAP_VERSION_STYLESHEET] = array('multichoice', '_choices' => $cssFiles);

$meta[action_plugin_combo_snippetsbootstrap::CONF_JQUERY_DOKU] = array('onoff');

$meta[action_plugin_combo_snippetsbootstrap::CONF_DISABLE_BACKEND_JAVASCRIPT] = array('onoff');

?>
