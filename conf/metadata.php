<?php

//configuration metadata describe properties of the settings as used by the Configuration Manager
//https://www.dokuwiki.org/devel:configuration#configuration_metadata

require_once (__DIR__ . "/../class/TplConstant.php");
require_once (__DIR__ . '/../class/TplUtility.php');


use ComboStrap\TplConstant;
use ComboStrap\TplUtility;

$meta[TplConstant::CONF_FOOTER] = array('string',
    "_caution" => "warning", // Show a warning
    "_pattern" => "/[a-zA-Z0-9]*/" // Only Accept alphanumeric characters
);

$meta[TplConstant::CONF_HEADER] = array('string',
    "_caution" => "warning", // Show a warning
    "_pattern" => "/[a-zA-Z0-9]*/" // Only Accept alphanumeric characters
);

$meta[TplConstant::CONF_SIDEKICK] = array('string',
    "_caution" => "warning", // Show a warning
    "_pattern" => "/[a-zA-Z0-9]*/" // Only Accept alphanumeric characters
);




/**
 * Do we use CDN when possible
 */
$meta[TplConstant::CONF_USE_CDN] = array('onoff');

/**
 * Do we print debug statement
 */
$meta['debug'] = array('onoff');

/**
 * The size of 1 rem in pixel
 */
$meta[TplConstant::CONF_REM_SIZE] = array('string');




$meta[TplConstant::CONF_GRID_COLUMNS]  = array('multichoice','_choices' => array('12','16'));

$meta['heightTopBar'] = array('string');

$meta['preloadCss'] = array('onoff');

$meta['privateToolbar'] = array('onoff');


$meta[TplConstant::CONF_BOOTSTRAP_VERSION]  = array('multichoice','_choices' => array('4.4.1','4.5.0'));

$cssFiles = array(TplConstant::DEFAULT_BOOTSTRAP_STYLESHEET);
$cssFiles = array_merge($cssFiles, TplUtility::getCustomStylesheet());
$meta[TplConstant::CONF_BOOTSTRAP_STYLESHEET] = array('multichoice','_choices' => $cssFiles);

?>
