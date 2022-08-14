<?php

use ComboStrap\Bootstrap;
use ComboStrap\TplUtility;
use dokuwiki\plugin\config\core\Configuration;

require_once(__DIR__ . '/../class/TplUtility.php');

/**
 *
 *
 * @group template_strap
 * @group templates
 */
class tplUtilityTest extends DokuWikiTest
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
     * Test that a wiki with an old header configuration
     * is saved to the old value
     *
     * The functionality scan for children page
     * with the same name and if found set the new configuration
     * when we try to get the value
     */
    public function testUpdateConfigurationWithOldValue()
    {

        /**
         * A switch to update the configuration
         * (Not done normally due to the hard coded constant DOKU_DATA. See more at {@link TplUtility::updateConfiguration()}
         */
        global $_REQUEST;
        $_REQUEST[TplUtility::COMBO_TEST_UPDATE] = true;

        /**
         * Creating a page in a children directory
         * with the old configuration
         */
        $oldConf = TplUtility::CONF_HEADER_OLD;
        $expectedValue = TplUtility::CONF_HEADER_OLD_VALUE;
        saveWikiText("ns:" . $oldConf, "Header page with the old", 'Script Test base');

        $strapName = "strap";
        $strapKey = TplUtility::CONF_HEADER_SLOT_PAGE_NAME;

        $value = TplUtility::getHeaderSlotPageName();
        $this->assertEquals($expectedValue, $value);

        $configuration = new Configuration();
        $settings = $configuration->getSettings();
        $key = "tpl____${strapName}____" . $strapKey;

        $setting = $settings[$key];
        $this->assertEquals(true, isset($setting));

        $formsOutput = $setting->out("conf");
        $formsOutputExpected = <<<EOF
\$conf['tpl']['$strapName']['$strapKey'] = '$expectedValue';

EOF;

        $this->assertEquals($formsOutputExpected, $formsOutput);


        global $config_cascade;
        $config = end($config_cascade['main']['local']);
        $conf = [];
        /** @noinspection PhpIncludeInspection */
        include $config;
        $this->assertEquals($expectedValue, $conf["tpl"]["strap"][$strapKey], "Good value in config");

        /**
         * The conf has been messed up
         * See {@link TplUtility::updateConfiguration()} for information
         */
        unset($_REQUEST[TplUtility::COMBO_TEST_UPDATE]);
        self::setUpBeforeClass();

    }

    public function testUpdateConfigurationForANewInstallation()
    {

        /**
         * A switch to update the configuration
         * (Not done normally due to the hard coded constant DOKU_DATA. See more at {@link TplUtility::updateConfiguration()}
         */
        global $_REQUEST;
        $_REQUEST[TplUtility::COMBO_TEST_UPDATE] = true;

        $expectedValue = "slot_header";
        $strapName = "strap";
        $strapKey = TplUtility::CONF_HEADER_SLOT_PAGE_NAME;

        $value = TplUtility::getHeaderSlotPageName();
        $this->assertEquals($expectedValue, $value);

        $configuration = new Configuration();
        $settings = $configuration->getSettings();
        $key = "tpl____${strapName}____" . $strapKey;

        $setting = $settings[$key];
        $this->assertEquals(true, isset($setting));

        $formsOutput = $setting->out("conf");
        $formsOutputExpected = <<<EOF
\$conf['tpl']['$strapName']['$strapKey'] = '$expectedValue';

EOF;

        $this->assertEquals($formsOutputExpected, $formsOutput);

        global $config_cascade;
        $config = end($config_cascade['main']['local']);
        $conf = [];
        /** @noinspection PhpIncludeInspection */
        include $config;
        $this->assertEquals($expectedValue, $conf["tpl"]["strap"][$strapKey], "Good value in config");

        /**
         * The conf has been messed up
         * See {@link TplUtility::updateConfiguration()} for information
         */
        unset($_REQUEST[TplUtility::COMBO_TEST_UPDATE]);
        self::setUpBeforeClass();

    }


}
