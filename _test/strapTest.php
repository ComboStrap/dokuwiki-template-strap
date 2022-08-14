<?php /** @noinspection PhpComposerExtensionStubsInspection */

use ComboStrap\Bootstrap;
use ComboStrap\FetcherRailBar;
use ComboStrap\Snippet;
use ComboStrap\SnippetSystem;
use ComboStrap\TplUtility;

require_once(__DIR__ . '/../class/TplUtility.php');
require_once(__DIR__ . '/../class/DomUtility.php');

/**
 *
 * Test the {@link tpl_strap_meta_header()
 *
 * @group template_strap
 * @group templates
 */
class strapTest extends DokuWikiTest
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




}
