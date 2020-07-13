<?php

//configuration metadata describe properties of the settings as used by the Configuration Manager
//https://www.dokuwiki.org/devel:configuration#configuration_metadata


use ComboStrap\TplUtility;

$meta['footer'] = array('string',
    "_caution" => "warning", // Show a warning
    "_pattern" => "/[a-zA-Z0-9]*/" // Only Accept alphanumeric characters
);

$meta['header'] = array('string',
    "_caution" => "warning", // Show a warning
    "_pattern" => "/[a-zA-Z0-9]*/" // Only Accept alphanumeric characters
);

$meta['sidekickbar'] = array('string',
    "_caution" => "warning", // Show a warning
    "_pattern" => "/[a-zA-Z0-9]*/" // Only Accept alphanumeric characters
);




// Do we use CDN when possible
$meta['cdn'] = array('onoff');

// Do we print debug statement
$meta['debug'] = array('onoff');

$meta['rem'] = array('string');




$meta['gridColumns']  = array('multichoice','_choices' => array('12','16'));

$meta['heightTopBar'] = array('string');

$meta['preloadCss'] = array('onoff');

$meta['privateToolbar'] = array('onoff');


$meta['bootstrapVersion']  = array('multichoice','_choices' => array('4.4.1','4.5.0'));
require_once (__DIR__ . '/../TplUtility.php');
$cssFiles=array("bootstrap.min.css");
$cssFiles = array_merge($cssFiles, TplUtility::getCustomStylesheet());
$meta['bootstrapStylesheet'] = array('multichoice','_choices' => $cssFiles);

?>
