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

    /**
     * An utilit function that test if the headers meta are still
     * on the page (ie response)
     * @param TestResponse $response
     * @param string $loginType
     * @param $scriptSignatures
     */
    private function checkMeta(TestResponse $response, $loginType, $scriptSignatures)
    {
        // No Css preloading
        $stylesheets = $response->queryHTML('link[rel="preload"]')->get();
        $this->assertEquals(0, sizeof($stylesheets));

        // Stylesheet
        $stylesheets = $response->queryHTML('link[rel="stylesheet"]')->get();
        $this->assertEquals(2, sizeof($stylesheets),"Two stylesheets for ${loginType}");
        $node = DomUtility::domElements2Attributes($stylesheets);
        $version = tpl_getConf('bootstrapVersion');
        $this->assertEquals('/./lib/tpl/strap/bootstrap/' . $version . '/bootstrap.min.css', $node[1]["href"]);
        $post = strpos($node[0]["href"], '/./lib/exe/css.php?t=strap');
        $this->assertEquals(0, $post, "The css php file is present for ${loginType}");

        /**
         * @var array|DomElement $scripts
         */
        $scripts = $response->queryHTML('script')->get();
        foreach ($scriptSignatures as $signatureToFind) {
            $strpos = false;
            foreach ($scripts as $script) {
                $haystack = $script->getAttribute("src") . $script->textContent;
                $strpos = strpos($haystack, $signatureToFind);
                if ($strpos !== false){
                    break;
                }
            }
            $this->assertTrue($strpos !== false, "Unable to find ($signatureToFind) for ${loginType}");
        }
        $this->assertEquals(5, sizeof($scripts));

    }

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
     * Test the {@link \Combostrap\TplUtility::handleBootstrapMetaHeaders()} function
     */
    public function test_handleBootStrapMetaHeaders_anonymous()
    {

        // Anonymous
        $pageId = 'start';
        saveWikiText($pageId, "Content", 'Script Test base');
        idx_addPage($pageId);

        $request = new TestRequest();
        $response = $request->get(array('id' => $pageId, '/doku.php'));
        $scriptsSignature = ['code.jquery.com/jquery', 'popper.min.js', 'bootstrap.min.js', 'JSINFO', 'js.php'];
        $this->checkMeta($response, "Anonymous",$scriptsSignature);


    }

    public function test_handleBootStrapMetaHeaders_loggedin()
    {

        $pageId = 'start';
        saveWikiText($pageId, "Content", 'Script Test base');
        idx_addPage($pageId);
        // Log in
        global $conf;
        $conf['useacl'] = 1;
        $user = 'admin';
        $conf['superuser'] = $user;
        $request = new TestRequest();
        $request->setServer('REMOTE_USER', $user);
        $response = $request->get(array('id' => $pageId, '/doku.php'));
        $scriptsSignature = ['jquery.php', 'popper.min.js', 'bootstrap.min.js', 'JSINFO', 'js.php'];
        $this->checkMeta($response,  "Logged in",$scriptsSignature);


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

    /**
     * Test that a toolbar is not shown when it's private
     * @throws Exception
     */
    public function test_privateToolbar()
    {
        TplUtility::setConf('privateToolbar',0);

        $pageId = 'start';
        saveWikiText($pageId, "Content", 'Script Test base');
        idx_addPage($pageId);

        $request = new TestRequest();
        $response = $request->get(array('id' => $pageId, '/doku.php'));

        $toolbarCount = $response->queryHTML('#dokuwiki__pagetools')->count();
        $this->assertEquals(1, $toolbarCount);

        // Anonymous user should not see it
        TplUtility::setConf('privateToolbar',1);
        $request = new TestRequest();
        $response = $request->get(array('id' => $pageId, '/doku.php'));
        $toolbarCount = $response->queryHTML('#dokuwiki__pagetools')->count();
        $this->assertEquals(0, $toolbarCount);

        // Connected user should see it
        $request = new TestRequest();
        $request->setServer('REMOTE_USER', 'auser');
        $response = $request->get(array('id' => $pageId, '/doku.php'));
        $toolbarCount = $response->queryHTML('#dokuwiki__pagetools')->count();
        $this->assertEquals(1, $toolbarCount);

    }


}
