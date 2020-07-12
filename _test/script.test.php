<?php

require_once (__DIR__.'/../tpl_lib_strap.php');

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
        tpl_loadConfig();


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
        $node = $this->domElements2Attributes($stylesheets);
        $version = tpl_getConf('bootstrapVersion');
        $this->assertEquals('/./lib/tpl/strap/css/' . $version . '/bootstrap.min.css', $node[1]["href"]);
        $post = strpos($node[0]["href"], '/./lib/exe/css.php?t=strap');
        $this->assertEquals(0, $post, "The css php file is present");

        // Javascript
        $scripts = $response->queryHTML('script')->get();
        $this->assertEquals(5, sizeof($scripts));
        $scriptsSignature = ['jquery', 'popper', 'bootstrap', 'JSINFO', 'js.php'];
        $i = 0;
        foreach ($scripts as $script) {
            $signatureToFind = $scriptsSignature[$i];
            $haystack = $script->getAttribute("src") . $script->textContent;
            $strpos = strpos($haystack, $signatureToFind);
            $this->assertNotFalse($strpos, "Unable to find "+$signatureToFind);
            $i++;
        }


    }

    /**
     * test the css preload configuration
     *
     */
    public function test_css_preload()
    {

        strapTplTest_setConf('preloadCss',1);

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

        $this->assertEquals(2, sizeof($node),"The stylesheet count should be 2");

        $version = tpl_getConf('bootstrapVersion');
        $post = strpos($node[0]["href"], '/./lib/exe/css.php?t=strap');
        $this->assertEquals(0, $post, "The css php file is present");
        $this->assertEquals('/./lib/tpl/strap/css/' . $version . '/bootstrap.min.css', $node[1]["href"]);


    }


    /**
     * @param DOMElement $domElements
     * @return array with one element by dom element  with its attributes
     *
     * This funcion was created because there is no way to get this information
     * from the phpQuery element
     */
    public function domElements2Attributes($domElements)
    {
        $nodes = array();
        foreach ($domElements as $key => $domElement) {
            $nodes[$key]=$this->extractsAttributes($domElement);
        }
        return $nodes;
    }

    /**
     * @param DOMElement $domElement
     * @return array with one element  with its attributes
     *
     * This function was created because there is no way to get this information
     * from the phpQuery element
     */
    public function extractsAttributes($domElement)
    {
        $node = array();
        if ($domElement->hasAttributes()) {
            foreach ($domElement->attributes as $attr) {
                $name = $attr->name;
                $value = $attr->value;
                $node[$name] = $value;
            }
        }
        return $node;
    }


}
