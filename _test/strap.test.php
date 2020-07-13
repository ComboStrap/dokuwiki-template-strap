<?php

use ComboStrap\DomUtility;
use ComboStrap\TplUtility;

require_once(__DIR__ . '/../TplUtility.php');
require_once(__DIR__ . '/../DomUtility.php');

/**
 *
 * Test the {@link tpl_strap_meta_header()
 *
 * @group template_strap
 * @group templates
 */
class template_strap_script_test extends DokuWikiTest
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
     * A simple test to test that the template is working
     * on every language
     */
    public function test_base()
    {

        $pageId = 'start';
        saveWikiText($pageId, "Content", 'Script Test base');
        idx_addPage($pageId);

        $request = new TestRequest();
        $response = $request->get(array('id' => $pageId, '/doku.php'));

        // No Css preloading
        $stylesheets = $response->queryHTML('link[rel="preload"]')->get();
        $this->assertEquals(0, sizeof($stylesheets));

        // Stylesheet
        $stylesheets = $response->queryHTML('link[rel="stylesheet"]')->get();
        $this->assertEquals(2, sizeof($stylesheets));
        $node = DomUtility::domElements2Attributes($stylesheets);
        $version = tpl_getConf('bootstrapVersion');
        $this->assertEquals('/./lib/tpl/strap/bootstrap/' . $version . '/bootstrap.min.css', $node[1]["href"]);
        $post = strpos($node[0]["href"], '/./lib/exe/css.php?t=strap');
        $this->assertEquals(0, $post, "The css php file is present");

        /**
         * @var DomElement $scripts
         */
        $scripts = $response->queryHTML('script')->get();
        $this->assertEquals(5, sizeof($scripts));
        $scriptsSignature = ['jquery', 'popper', 'bootstrap', 'JSINFO', 'js.php'];
        $i = 0;
        foreach ($scripts as $script) {
            $signatureToFind = $scriptsSignature[$i];
            $haystack = $script->getAttribute("src") . $script->textContent;
            $strpos = strpos($haystack, $signatureToFind);
            $this->assertTrue($strpos !== false, "Unable to find " . $signatureToFind);
            $i++;
        }


    }

    /**
     * test the css preload configuration
     *
     * @throws Exception
     */
    public function test_css_preload()
    {

        TplUtility::setConf('preloadCss', 1);

        $pageId = 'start';
        saveWikiText($pageId, "Content", 'Script Test base');
        idx_addPage($pageId);

        $request = new TestRequest();
        $response = $request->get(array('id' => $pageId, '/doku.php'));

        $stylesheets = $response->queryHTML('link[rel="preload"]')->get();


        $node = array();
        foreach ($stylesheets as $key => $stylesheet) {
            if ($stylesheet->hasAttributes()) {
                foreach ($stylesheet->attributes as $attr) {
                    $name = $attr->name;
                    $value = $attr->value;
                    $node[$key][$name] = $value;
                }
            }
        }

        $this->assertEquals(2, sizeof($node), "The stylesheet count should be 2");

        $version = tpl_getConf('bootstrapVersion');
        $post = strpos($node[0]["href"], '/./lib/exe/css.php?t=strap');
        $this->assertEquals(0, $post, "The css php file is present");
        $this->assertEquals('/./lib/tpl/strap/bootstrap/' . $version . '/bootstrap.min.css', $node[1]["href"]);


    }





    /**
     * Test the {@link \Combostrap\TplUtility::buildBootstrapMetas()} function
     * @throws Exception
     */
    public function test_buildBootstrapMetas()
    {
        $metas = TplUtility::buildBootstrapMetas("4.5.0");
        $this->assertEquals(4, sizeof($metas));
        $this->assertEquals("bootstrap.min.css", $metas["css"]["file"]);

        TplUtility::setConf("bootstrapStylesheet", "bootstrap.16col");
        $metas = TplUtility::buildBootstrapMetas("4.5.0");
        $this->assertEquals(4, sizeof($metas));
        $this->assertEquals("bootstrap.16col.min.css", $metas["css"]["file"]);
    }


    /**
     * Test the {@link \Combostrap\TplUtility::getBootstrapMetaHeaders()} function
     * @throws Exception
     */
    public function test_getBootstrapMetaHeaders()
    {
        $metas = TplUtility::getBootstrapMetaHeaders();
        $this->assertEquals(2, sizeof($metas));

        $this->assertEquals(3, sizeof($metas['script']), "There is three js script");
        $this->assertEquals(1, sizeof($metas['link']), "There is one css script");


    }

    /**
     * Test the {@link \Combostrap\TplUtility::getCustomStylesheet()} function
     */
    public function test_getCustomCssFiles()
    {

        // Default
        $files = TplUtility::getCustomStylesheet();
        $this->assertEquals(1, sizeof($files), "There is one css script");

        // With a custom file
        $destination = __DIR__ . '/../bootstrap/bootstrapLocal.json';
        copy (__DIR__.'/resources/bootstrapLocal.json', $destination);
        $files = TplUtility::getCustomStylesheet();
        $this->assertEquals(2, sizeof($files), "There is two css script");
        unlink($destination);


    }

    /**
     * Test that a detail page is rendering
     */
    public function test_detail_php()
    {
        $pageId = 'start';
        saveWikiText($pageId, "Content", 'Script Test base');
        idx_addPage($pageId);

        $request = new TestRequest();
        $response = $request->get(array('id' => $pageId, '/detail.php'));

        $generator = $response->queryHTML('meta[name="generator"]')->attr("content");
        $this->assertEquals("DokuWiki", $generator);

    }

    /**
     * Test that a media page is rendering
     */
    public function test_media_manager_php()
    {
        $pageId = 'start';
        saveWikiText($pageId, "Content", 'Script Test base');
        idx_addPage($pageId);

        $request = new TestRequest();
        $response = $request->get(array('id' => $pageId, '/mediamanager.php'));

        $generator = $response->queryHTML('meta[name="generator"]')->attr("content");
        $this->assertEquals("DokuWiki", $generator);

    }


}
