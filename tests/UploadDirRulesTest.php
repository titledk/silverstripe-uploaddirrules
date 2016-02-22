<?php
/**
 * Class UploadDirRulesTest.
 *
 * @author Anselm Christophersen <ac@title.dk>
 * @copyright Copyright (c) 2015, Title Web Solutions
 */
class UploadDirRulesTest extends SapphireTest
{
    public function setUp()
    {
        parent::setUp();

        // create 2 pages
        for ($i = 0; $i < 2; ++$i) {
            $page = new Page(array('Title' => "Page $i"));
            $page->write();
            $page->publish('Stage', 'Live');
        }

        // reset configuration for the test.
        Config::nest();
        Config::inst()->update('Foo', 'bar', 'Hello!');
    }

    public function tearDown()
    {
        // restores the config variables
        Config::unnest();

        parent::tearDown();
    }

    public function test_calc_base_directory_for_object()
    {

        //Page should be "pages"
        $page = Page::get()->first();
        $this->assertEquals(
            'pages',
            UploadDirRules::calc_base_directory_for_object($page)
        );

        //SiteConfig should be "site"
        $sc = SiteConfig::current_site_config();
        $this->assertEquals(
            'site',
            UploadDirRules::calc_base_directory_for_object($sc)
        );
    }
}
