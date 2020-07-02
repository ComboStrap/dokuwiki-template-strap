<?php


/**
 *
 *
 * @group template_strap
 * @group templates
 */
class template_strap_php_test extends DokuWikiTest
{


    /**
     * A simple test to test that the template is working
     * on every language
     */
    public function test_base()
    {

        global $conf;
        // https://www.dokuwiki.org/config:jquerycdn
        $conf ['jquerycdn'] = 'cdnjs';

        $pageId = syntax_plugin_webcomponent_button::getTag().':header:test_base';
        saveWikiText($pageId, "Content", 'Header Test base');
        idx_addPage($pageId);

        $request = new TestRequest();
        $response = $request->get(array('id' => $pageId, '/doku.php'));
        $expected = 'DokuWiki';

        $generator = $response->queryHTML('meta[name="generator"]')->attr("content");
        $this->assertEquals($expected, $generator);


    }


}
