<?php

use ComboStrap\TplUtility;

require_once(__DIR__ . '/../class/TplUtility.php');

/**
 *
 *
 * @group template_strap
 * @group templates
 */
class TplUtilityTest extends DokuWikiTest
{

    public function setUp()
    {

        global $conf;
        parent::setUp();
        $conf ['template'] = 'strap';

        /**
         * static variable bug in the {@link tpl_getConf()}
         * that does not load the configuration twice
         */
        TplUtility::reloadConf();

    }

    /**
     * Test the {@link \Combostrap\TplUtility::getStylesheetsForMetadataConfiguration()} function
     */
    public function testGetStylesheetsForMetadataConfiguration()
    {

        // Local file created by the users with their own stylesheet
        $destination = __DIR__ . '/../bootstrap/bootstrapLocal.json';
        // If we debug, it may not be deleted
        if (file_exists($destination)) {
            unlink($destination);
        }

        // Default
        $configurationList = TplUtility::getStylesheetsForMetadataConfiguration();
        $distributionStylesheet = 51;
        $this->assertEquals($distributionStylesheet, sizeof($configurationList), "Number of stylesheet");


        copy(__DIR__ . '/resources/bootstrapLocal.json', $destination);
        $configurationList = TplUtility::getStylesheetsForMetadataConfiguration();
        $styleSheetWithCustom = $distributionStylesheet + 1;
        $this->assertEquals($styleSheetWithCustom, sizeof($configurationList), "There is one stylesheet more");
        unlink($destination);


    }

    public function testGetStyleSheetAndBootstrapVersionConf()
    {
        $stylesheet = "bootstrap.16col";
        $boostrapVersion = "4.5.0";
        TplUtility::setConf(TplUtility::CONF_BOOTSTRAP_VERSION_STYLESHEET, $boostrapVersion . TplUtility::BOOTSTRAP_VERSION_STYLESHEET_SEPARATOR . $stylesheet);
        $actualStyleSheet = TplUtility::getStyleSheetConf();
        $this->assertEquals($stylesheet, $actualStyleSheet);
        $actualBootStrapVersion = TplUtility::getBootStrapVersion();
        $this->assertEquals($boostrapVersion, $actualBootStrapVersion);
    }


    /**
     * Test the {@link \Combostrap\TplUtility::buildBootstrapMetas()} function
     * that returns the needed bootstrap resources
     * @throws Exception
     */
    public function test_buildBootstrapMetas()
    {
        $boostrapVersion = "4.5.0";
        $metas = TplUtility::buildBootstrapMetas($boostrapVersion);
        $this->assertEquals(4, sizeof($metas));
        $this->assertEquals("bootstrap.min.css", $metas["css"]["file"]);

        TplUtility::setConf(TplUtility::CONF_BOOTSTRAP_VERSION_STYLESHEET, $boostrapVersion . TplUtility::BOOTSTRAP_VERSION_STYLESHEET_SEPARATOR . "16col");
        $metas = TplUtility::buildBootstrapMetas($boostrapVersion);
        $this->assertEquals(4, sizeof($metas));
        $this->assertEquals("bootstrap.16col.min.css", $metas["css"]["file"]);

        TplUtility::setConf(TplUtility::CONF_BOOTSTRAP_VERSION_STYLESHEET, $boostrapVersion . TplUtility::BOOTSTRAP_VERSION_STYLESHEET_SEPARATOR . "simplex");
        $metas = TplUtility::buildBootstrapMetas($boostrapVersion);
        $this->assertEquals(4, sizeof($metas));
        $this->assertEquals("bootstrap.simplex.min.css", $metas["css"]["file"]);
        $this->assertEquals("https://cdn.jsdelivr.net/npm/bootswatch@4.5.0/dist/simplex/bootstrap.min.css", $metas["css"]["url"]);

    }

    /**
     * Testing the {@link TplUtility::renderBar()}
     */
    public function testBarCache()
    {

        $sidebarName = "sidebar";
        $sidebarId = ":".$sidebarName;
        saveWikiText($sidebarId, "=== title ===", "");
        $metadata = p_read_metadata($sidebarId);
        p_save_metadata($sidebarName, $metadata);
        global $ID;
        $ID = ":namespace:whatever";
        $data = TplUtility::renderBar($sidebarName);
        $this->assertNotEmpty($data);
        /**
         * TODO:  We should test that the file are not the same with bar plugin that shows the files of a namespace
         * The test was done manually
         */

    }


}
