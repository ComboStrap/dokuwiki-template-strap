<?php

use ComboStrap\DomUtility;
use ComboStrap\TplConstant;
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
class template_strap_script_test extends DokuWikiTest
{

    /**
     * An utilit function that test if the headers meta are still
     * on the page (ie response)
     * @param TestResponse $response
     * @param $selector - the DOM elementselector
     * @param $attr - the attribute to check
     * @param $scriptSignatures - the pattern signature to find
     * @param string $loginType - the login type (anonymous, logged in, ...)
     */
    private function checkMeta(TestResponse $response, $selector, $attr, $scriptSignatures,$loginType )
    {


        /**
         * @var array|DomElement $scripts
         */
        $domElements = $response->queryHTML($selector)->get();

        foreach ($scriptSignatures as $signatureToFind) {
            $patternFound = 0;
            foreach ($domElements as $domElement) {
                $haystack = $domElement->getAttribute($attr) . $domElement->textContent;
                $patternFound = preg_match("/$signatureToFind/i", $haystack);
                if ($patternFound===1) {
                    break;
                }
            }
            $this->assertTrue($patternFound !== 0, "Unable to find ($signatureToFind) for ${loginType}");
        }

        $this->assertEquals(sizeof($scriptSignatures), sizeof($domElements),"The number of signatures should be the same for ($selector)");

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
     * Test the default configuration
     *
     * Test the {@link \Combostrap\TplUtility::handleBootstrapMetaHeaders()} function
     */
    public function test_handleBootStrapMetaHeaders_anonymous_default()
    {

        // Anonymous
        $pageId = 'start';
        saveWikiText($pageId, "Content", 'Script Test base');
        idx_addPage($pageId);

        $request = new TestRequest();
        $response = $request->get(array('id' => $pageId, '/doku.php'));

        $cdn = tpl_getConf(TplConstant::CONF_USE_CDN);
        $this->assertEquals(1,$cdn,"The CDN is by default on");

        /**
         * Script signature
         * CDN is on by default
         *
         * js.php is needed for custom script such as a consent box
         */
        $version = tpl_getConf(TplConstant::CONF_BOOTSTRAP_VERSION);
        $scriptsSignature = ["jquery.com\/jquery-(.*).js", "cdn.jsdelivr.net\/npm\/popper.js", "stackpath.bootstrapcdn.com\/bootstrap\/$version\/js\/bootstrap.min.js", 'JSINFO', 'js.php'];
        $this->checkMeta($response,  'script',"src",$scriptsSignature,"Anonymous");

        /**
         * Stylesheet signature (href)
         */
        $stylsheetSignature = ["stackpath.bootstrapcdn.com\/bootstrap\/$version\/css\/bootstrap.min.css",'\/lib\/exe\/css.php\?t\=strap'];
        $this->checkMeta($response,  'link[rel="stylesheet"]',"href",$stylsheetSignature,"Anonymous");


    }

    /**
     * @throws Exception
     */
    public function test_handleBootStrapMetaHeaders_anonymous_nocdn()
    {

        /**
         * CDN is on by default, disable
         */
        TplUtility::setConf(TplConstant::CONF_USE_CDN,0);

        // Anonymous
        $pageId = 'start';
        saveWikiText($pageId, "Content", 'Script Test base');
        idx_addPage($pageId);

        $request = new TestRequest();
        $response = $request->get(array('id' => $pageId, '/doku.php'));

        /**
         * Script signature
         */
        $version = tpl_getConf('bootstrapVersion');
        $localDirPattern =  '\/lib\/tpl\/strap\/bootstrap\/' . $version ;
        $scriptsSignature = ["$localDirPattern\/jquery-(.*).js", "$localDirPattern\/popper.min.js", "$localDirPattern\/bootstrap.min.js", 'JSINFO', 'js.php'];
        $this->checkMeta($response,  'script',"src",$scriptsSignature,"Anonymous");

        /**
         * Stylesheet signature (href)
         */
        $stylsheetSignature = ["$localDirPattern\/bootstrap.min.css",'\/lib\/exe\/css.php\?t\=strap'];
        $this->checkMeta($response,  'link[rel="stylesheet"]',"href",$stylsheetSignature,"Anonymous");

    }

    /**
     * When a user is logged in, the CDN is no more
     */
    public function test_handleBootStrapMetaHeaders_loggedin_default()
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

        /**
         * No Css preloading
         */
        $stylesheets = $response->queryHTML('link[rel="preload"]')->get();
        $this->assertEquals(0, sizeof($stylesheets));

        /**
         * Script signature
         */
        $version = tpl_getConf('bootstrapVersion');

        $scriptsSignature = ["jquery.php","cdn.jsdelivr.net\/npm\/popper.js", "stackpath.bootstrapcdn.com\/bootstrap\/$version\/js\/bootstrap.min.js", 'JSINFO', 'js.php'];
        $this->checkMeta($response,  'script',"src",$scriptsSignature,"Logged in");

        /**
         * Stylesheet signature (href)
         */
        $stylsheetSignature = ["stackpath.bootstrapcdn.com\/bootstrap\/$version\/css\/bootstrap.min.css",'\/lib\/exe\/css.php\?t\=strap'];
        $this->checkMeta($response,  'link[rel="stylesheet"]',"href",$stylsheetSignature,"Logged in");


    }

    /**
     * test the css preload configuration
     *
     * @throws Exception
     */
    public function test_css_preload_anonymous()
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

        $version = tpl_getConf(TplConstant::CONF_BOOTSTRAP_VERSION);
        $stylsheetSignature = ["stackpath.bootstrapcdn.com\/bootstrap\/$version\/css\/bootstrap.min.css",'\/lib\/exe\/css.php\?t\=strap'];
        $this->checkMeta($response,  'link[rel="stylesheet"]',"href",$stylsheetSignature,"Anonymous");



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
